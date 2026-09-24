<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Models\Customer;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitManagementTest extends TestCase
{
    use RefreshDatabase;

    public function unit_can_be_created_with_pending_status(): void
    {
        $customer = Customer::factory()->create();

        $trip = Trip::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $unit = Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => '123456789012345',
            'status' => UnitStatus::PENDING,
        ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => '123456789012345',
            'status' => UnitStatus::PENDING->value,
        ]);

        $this->assertSame(
            UnitStatus::PENDING,
            $unit->status
        );
    }

    public function admin_can_manage_units(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $unit = Unit::factory()->create();

        $this->assertTrue(
            $admin->can('viewAny', Unit::class)
        );

        $this->assertTrue(
            $admin->can('view', $unit)
        );

        $this->assertTrue(
            $admin->can('update', $unit)
        );

        $this->assertTrue(
            $admin->can('delete', $unit)
        );
    }

    public function technician_cannot_manage_units_globally(): void
    {
        $technician = User::factory()->create([
            'role' => 'TEKNISI',
            'is_active' => true,
        ]);

        $unit = Unit::factory()->create();

        $this->assertFalse(
            $technician->can('viewAny', Unit::class)
        );

        $this->assertFalse(
            $technician->can('view', $unit)
        );

        $this->assertFalse(
            $technician->can('update', $unit)
        );

        $this->assertFalse(
            $technician->can('delete', $unit)
        );
    }
}