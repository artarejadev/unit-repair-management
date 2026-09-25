<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SparepartManagementTest extends TestCase
{
    use RefreshDatabase;

    public function admin_can_manage_spareparts(): void
    {
        $admin = User::create([
            'name' => 'Admin Sparepart Test',
            'email' => 'admin-sparepart@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $sparepart = Sparepart::create([
            'name' => 'LCD iPhone 11',
        ]);

        $this->assertTrue(
            $admin->can('viewAny', Sparepart::class)
        );

        $this->assertTrue(
            $admin->can('view', $sparepart)
        );

        $this->assertTrue(
            $admin->can('create', Sparepart::class)
        );

        $this->assertTrue(
            $admin->can('update', $sparepart)
        );

        $this->assertTrue(
            $admin->can('delete', $sparepart)
        );
    }

    public function technician_cannot_manage_spareparts(): void
    {
        $technician = User::create([
            'name' => 'Teknisi Sparepart Test',
            'email' => 'technician-sparepart@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::TEKNISI,
            'is_active' => true,
        ]);

        $sparepart = Sparepart::create([
            'name' => 'LCD iPhone 11',
        ]);

        $this->assertFalse(
            $technician->can('viewAny', Sparepart::class)
        );

        $this->assertFalse(
            $technician->can('view', $sparepart)
        );

        $this->assertFalse(
            $technician->can('create', Sparepart::class)
        );

        $this->assertFalse(
            $technician->can('update', $sparepart)
        );

        $this->assertFalse(
            $technician->can('delete', $sparepart)
        );
    }
}