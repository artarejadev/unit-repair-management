<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\Customer;
use App\Models\RepairType;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\UnitSparepart;
use App\Models\User;
use App\Services\Stock\UnitSparepartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitSparepartTest extends TestCase
{
    use RefreshDatabase;

    public function using_sparepart_reduces_stock_and_creates_unit_usage(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-unit-sparepart@test.local',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $trip = Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => 'TRIP-SP-001',
            'trip_date' => now()->toDateString(),
        ]);

        $unit = Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => 'IMEI-SP-001',
        ]);

        $sparepart = Sparepart::create([
            'name' => 'LCD iPhone 11',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $usage = app(UnitSparepartService::class)->use(
            unit: $unit,
            sparepart: $sparepart,
            quantity: 2,
            user: $admin,
        );

        $sparepart->refresh();

        $this->assertEquals(8, $sparepart->stock_qty);

        $this->assertEquals($unit->id, $usage->unit_id);
        $this->assertEquals($sparepart->id, $usage->sparepart_id);
        $this->assertEquals(2, $usage->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'movement_type' => StockMovementType::OUT->value,
            'quantity' => 2,
            'before_qty' => 10,
            'after_qty' => 8,
            'reference' => 'UNIT:' . $unit->imei,
            'user_id' => $admin->id,
        ]);
    }

    public function unit_sparepart_cannot_be_updated_or_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-unit-sparepart-policy@test.local',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Policy',
        ]);

        $trip = Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => 'TRIP-SP-002',
            'trip_date' => now()->toDateString(),
        ]);

        $unit = Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => 'IMEI-SP-002',
        ]);

        $sparepart = Sparepart::create([
            'name' => 'Battery iPhone 11',
        ]);

        $usage = UnitSparepart::create([
            'unit_id' => $unit->id,
            'sparepart_id' => $sparepart->id,
            'quantity' => 1,
        ]);

        $this->actingAs($admin);

        $this->assertFalse($admin->can('update', $usage));
        $this->assertFalse($admin->can('delete', $usage));
    }
}