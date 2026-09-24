<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\User;
use App\Policies\TripPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_have_multiple_trips(): void
    {
        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $customer->trips()->create([
            'trip_number' => 'TRIP-001',
            'trip_date' => now()->toDateString(),
        ]);

        $customer->trips()->create([
            'trip_number' => 'TRIP-002',
            'trip_date' => now()->toDateString(),
        ]);

        $this->assertCount(
            2,
            $customer->fresh()->trips
        );
    }

    public function test_admin_can_manage_trip(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $trip = Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => 'TRIP-001',
            'trip_date' => now()->toDateString(),
        ]);

        $policy = new TripPolicy();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $trip));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $trip));
        $this->assertTrue($policy->delete($admin, $trip));
    }

    public function test_technician_cannot_manage_trip(): void
    {
        $technician = User::factory()->create([
            'role' => UserRole::TEKNISI,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $trip = Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => 'TRIP-001',
            'trip_date' => now()->toDateString(),
        ]);

        $policy = new TripPolicy();

        $this->assertFalse($policy->viewAny($technician));
        $this->assertFalse($policy->view($technician, $trip));
        $this->assertFalse($policy->create($technician));
        $this->assertFalse($policy->update($technician, $trip));
        $this->assertFalse($policy->delete($technician, $trip));
    }

    public function test_trip_with_units_cannot_be_deleted(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $trip = Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => 'TRIP-001',
            'trip_date' => now()->toDateString(),
        ]);

        Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => '123456789012345',
            'status' => UnitStatus::PENDING,
        ]);

        $policy = new TripPolicy();

        $this->assertFalse(
            $policy->delete($admin, $trip)
        );
    }

    public function test_empty_trip_can_be_deleted(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $trip = Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => 'TRIP-DELETE',
            'trip_date' => now()->toDateString(),
        ]);

        $policy = new TripPolicy();

        $this->assertTrue(
            $policy->delete($admin, $trip)
        );
    }
}