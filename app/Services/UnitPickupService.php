<?php

namespace App\Services;

use App\Enums\UnitStatus;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UnitPickupService
{
    public function markAsPickedUp(Unit $unit, User $user): Unit
    {
        if (! $user->isAdmin()) {
            abort(403);
        }

        if ($unit->status !== UnitStatus::DITAGIHKAN) {
            throw ValidationException::withMessages([
                'unit' => 'Hanya unit berstatus DITAGIHKAN yang dapat ditandai sudah diambil.',
            ]);
        }

        $unit->update([
            'status' => UnitStatus::DIAMBIL,
        ]);

        return $unit->refresh();
    }
}