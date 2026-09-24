<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Unit;
use App\Models\UnitAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function admin_can_create_unit_assignment(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $technician = User::factory()->create([
            'role' => UserRole::TEKNISI,
            'is_active' => true,
        ]);

        $unit = Unit::factory()->create();

        $this->assertTrue(
            $admin->can('create', UnitAssignment::class)
        );

        $assignment = UnitAssignment::create([
            'unit_id' => $unit->id,
            'technician_id' => $technician->id,
            'assigned_at' => now(),
        ]);

        $this->assertDatabaseHas('unit_assignments', [
            'id' => $assignment->id,
            'unit_id' => $unit->id,
            'technician_id' => $technician->id,
        ]);
    }

    public function technician_cannot_manage_assignments(): void
    {
        $technician = User::factory()->create([
            'role' => UserRole::TEKNISI,
            'is_active' => true,
        ]);

        $assignment = UnitAssignment::factory()->create();

        $this->assertFalse(
            $technician->can('view', $assignment)
        );

        $this->assertFalse(
            $technician->can('create', UnitAssignment::class)
        );

        $this->assertFalse(
            $technician->can('update', $assignment)
        );

        $this->assertFalse(
            $technician->can('delete', $assignment)
        );
    }

    public function a_new_assignment_ends_the_previous_active_assignment(): void
    {
        $technicianOne = User::factory()->create([
            'role' => UserRole::TEKNISI,
            'is_active' => true,
        ]);

        $technicianTwo = User::factory()->create([
            'role' => UserRole::TEKNISI,
            'is_active' => true,
        ]);

        $unit = Unit::factory()->create();

        $firstAssignment = UnitAssignment::create([
            'unit_id' => $unit->id,
            'technician_id' => $technicianOne->id,
            'assigned_at' => now()->subHour(),
        ]);

        UnitAssignment::query()
            ->where('unit_id', $unit->id)
            ->whereNull('ended_at')
            ->update([
                'ended_at' => now(),
            ]);

        $secondAssignment = UnitAssignment::create([
            'unit_id' => $unit->id,
            'technician_id' => $technicianTwo->id,
            'assigned_at' => now(),
        ]);

        $this->assertNotNull(
            $firstAssignment->fresh()->ended_at
        );

        $this->assertNull(
            $secondAssignment->fresh()->ended_at
        );

        $this->assertSame(
            $technicianTwo->id,
            $unit->fresh()->currentAssignment->technician_id
        );
    }
}