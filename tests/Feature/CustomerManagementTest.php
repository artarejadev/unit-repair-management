<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
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

    public function test_admin_can_manage_customers(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $policy = new \App\Policies\CustomerPolicy();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $customer));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $customer));
        $this->assertTrue($policy->delete($admin, $customer));
    }

    public function test_technician_cannot_manage_customers(): void
    {
        $technician = User::factory()->create([
            'role' => UserRole::TEKNISI,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $policy = new \App\Policies\CustomerPolicy();

        $this->assertFalse($policy->viewAny($technician));
        $this->assertFalse($policy->view($technician, $customer));
        $this->assertFalse($policy->create($technician));
        $this->assertFalse($policy->update($technician, $customer));
        $this->assertFalse($policy->delete($technician, $customer));
    }

    public function test_customer_with_trip_cannot_be_deleted(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer With Trip',
        ]);

        $customer->trips()->create([
            'trip_number' => 'TRIP-001',
            'trip_date' => now()->toDateString(),
        ]);

        $policy = new \App\Policies\CustomerPolicy();

        $this->assertFalse(
            $policy->delete($admin, $customer)
        );
    }

    public function test_customer_without_transaction_can_be_deleted(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Delete Test',
        ]);

        $policy = new \App\Policies\CustomerPolicy();

        $this->assertTrue(
            $policy->delete($admin, $customer)
        );
    }
}