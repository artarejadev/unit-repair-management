<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Models\Customer;
use App\Models\RepairType;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\UnitRepair;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use App\Enums\UserRole;
use App\Models\User;

class UnitRepairManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(string $name = 'Customer Test'): Customer
    {
        return Customer::create([
            'name' => $name,
            'phone' => '081234567890',
            'email' => 'customer@example.com',
        ]);
    }

    private function createTrip(
        Customer $customer,
        string $tripNumber = 'TRIP-TEST-001'
    ): Trip {
        return Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => $tripNumber,
            'trip_date' => now()->toDateString(),
        ]);
    }

    private function createUnit(Customer $customer, Trip $trip, string $imei): Unit
    {
        return Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => $imei,
            'status' => UnitStatus::PENDING,
        ]);
    }

    private function createRepairType(
        string $name = 'Ganti LCD',
        float $price = 50000
    ): RepairType {
        return RepairType::create([
            'name' => $name,
            'default_price' => $price,
        ]);
    }

    #[Test]
    public function repair_always_snapshots_current_master_price_when_created(): void
    {
        $customer = $this->createCustomer();
        $trip = $this->createTrip($customer);

        $unit = $this->createUnit(
            $customer,
            $trip,
            '123456789012345'
        );

        $repairType = $this->createRepairType(
            'Ganti LCD',
            50000
        );

        $unitRepair = UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,

            // Sengaja salah.
            'price' => 999999,
        ]);

        $this->assertSame(
            '50000.00',
            $unitRepair->fresh()->price
        );
    }

    #[Test]
    public function changing_master_price_does_not_change_existing_unit_repair(): void
    {
        $customer = $this->createCustomer();
        $trip = $this->createTrip($customer);

        $unit = $this->createUnit(
            $customer,
            $trip,
            '223456789012345'
        );

        $repairType = $this->createRepairType(
            'Ganti LCD',
            50000
        );

        $unitRepair = UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,
        ]);

        $repairType->update([
            'default_price' => 60000,
        ]);

        $this->assertSame(
            '50000.00',
            $unitRepair->fresh()->price
        );

        $this->assertSame(
            '60000.00',
            $repairType->fresh()->default_price
        );
    }

    #[Test]
    public function new_unit_repair_uses_new_master_price(): void
    {
        $customer = $this->createCustomer();
        $trip = $this->createTrip($customer);

        $unitA = $this->createUnit(
            $customer,
            $trip,
            '323456789012345'
        );

        $unitB = $this->createUnit(
            $customer,
            $trip,
            '423456789012345'
        );

        $repairType = $this->createRepairType(
            'Ganti LCD',
            50000
        );

        $repairA = UnitRepair::create([
            'unit_id' => $unitA->id,
            'repair_type_id' => $repairType->id,
        ]);

        $repairType->update([
            'default_price' => 60000,
        ]);

        $repairB = UnitRepair::create([
            'unit_id' => $unitB->id,
            'repair_type_id' => $repairType->id,
        ]);

        $this->assertSame(
            '50000.00',
            $repairA->fresh()->price
        );

        $this->assertSame(
            '60000.00',
            $repairB->fresh()->price
        );
    }

    #[Test]
    public function admin_can_set_override_price(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-price@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $customer = $this->createCustomer();
        $trip = $this->createTrip($customer);

        $unit = $this->createUnit(
            $customer,
            $trip,
            '523456789012345'
        );

        $repairType = $this->createRepairType(
            'Ganti LCD',
            50000
        );

        $unitRepair = UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,
        ]);

        $this->actingAs($admin);

        $unitRepair->update([
            'override_price' => 40000,
        ]);

        $this->assertSame(
            '40000.00',
            $unitRepair->fresh()->override_price
        );

        $this->assertSame(
            40000.0,
            $unitRepair->fresh()->final_price
        );
    }

    #[Test]
    public function technician_cannot_change_override_price(): void
    {
        $technician = User::create([
            'name' => 'Teknisi Test',
            'email' => 'technician-price@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::TEKNISI,
            'is_active' => true,
        ]);

        $customer = $this->createCustomer(
            'Customer Technician Test'
        );

        $trip = $this->createTrip(
            $customer,
            'TRIP-TECH-001'
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '623456789012345'
        );

        $repairType = $this->createRepairType(
            'Ganti LCD',
            50000
        );

        $unitRepair = UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,
        ]);

        $this->actingAs($technician);

        $this->expectException(
            \Illuminate\Auth\Access\AuthorizationException::class
        );

        $unitRepair->update([
            'override_price' => 40000,
        ]);
    }

    #[Test]
    public function override_price_does_not_change_master_price(): void
    {
        $admin = User::create([
            'name' => 'Admin Override Test',
            'email' => 'admin-override@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $customer = $this->createCustomer(
            'Customer Override Test'
        );

        $trip = $this->createTrip(
            $customer,
            'TRIP-OVERRIDE-001'
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '723456789012345'
        );

        $repairType = $this->createRepairType(
            'Ganti LCD',
            50000
        );

        $unitRepair = UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,
        ]);

        $this->actingAs($admin);

        $unitRepair->update([
            'override_price' => 40000,
        ]);

        $this->assertSame(
            '40000.00',
            $unitRepair->fresh()->override_price
        );

        $this->assertSame(
            '50000.00',
            $repairType->fresh()->default_price
        );
    }
}