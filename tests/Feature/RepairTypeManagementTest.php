<?php

namespace Tests\Feature;

use App\Models\RepairType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RepairTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function admin_can_manage_repair_types(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->assertTrue(
            $admin->can('viewAny', RepairType::class)
        );

        $this->assertTrue(
            $admin->can('create', RepairType::class)
        );

        $repairType = RepairType::create([
            'name' => 'Ganti LCD',
            'default_price' => 50000,
        ]);

        $this->assertTrue(
            $admin->can('view', $repairType)
        );

        $this->assertTrue(
            $admin->can('update', $repairType)
        );

        $this->assertTrue(
            $admin->can('delete', $repairType)
        );
    }

    public function technician_cannot_manage_repair_types(): void
    {
        $technician = User::factory()->create([
            'role' => 'TEKNISI',
            'is_active' => true,
        ]);

        $repairType = RepairType::create([
            'name' => 'Ganti LCD',
            'default_price' => 50000,
        ]);

        $this->assertFalse(
            $technician->can('viewAny', RepairType::class)
        );

        $this->assertFalse(
            $technician->can('view', $repairType)
        );

        $this->assertFalse(
            $technician->can('create', RepairType::class)
        );

        $this->assertFalse(
            $technician->can('update', $repairType)
        );

        $this->assertFalse(
            $technician->can('delete', $repairType)
        );
    }
}