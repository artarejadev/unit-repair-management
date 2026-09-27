<?php

namespace App\Services\Units;

use App\Enums\UnitStatus;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TechnicianUnitWorkflowService
{
    public function start(Unit $unit, User $technician): Unit
    {
        return DB::transaction(function () use ($unit, $technician): Unit {
            $unit = Unit::query()
                ->lockForUpdate()
                ->findOrFail($unit->id);

            if (! $unit->isAssignedTo($technician)) {
                throw new RuntimeException(
                    'Unit ini bukan assignment Teknisi tersebut.'
                );
            }

            // if ($unit->status !== UnitStatus::PENDING) {
            //     throw new RuntimeException(
            //         'Unit hanya dapat dimulai dari status PENDING.'
            //     );
            // }

            if (! in_array(
                $unit->status,
                [
                    UnitStatus::PENDING,
                    UnitStatus::REWORK,
                ],
                true
            )) {
                throw new RuntimeException(
                    'Unit hanya dapat dimulai dari status PENDING atau REWORK.'
                );
            }

            $unit->status = UnitStatus::PROSES;
            $unit->save();

            return $unit->refresh();
        });
    }

    public function finish(Unit $unit, User $technician): Unit
    {
        return DB::transaction(function () use ($unit, $technician): Unit {
            $unit = Unit::query()
                ->lockForUpdate()
                ->findOrFail($unit->id);

            if (! $unit->isAssignedTo($technician)) {
                throw new RuntimeException(
                    'Unit ini bukan assignment Teknisi tersebut.'
                );
            }

            if ($unit->status !== UnitStatus::PROSES) {
                throw new RuntimeException(
                    'Unit hanya dapat diselesaikan dari status PROSES.'
                );
            }

            $unit->status = UnitStatus::SELESAI;
            $unit->save();

            return $unit->refresh();
        });
    }
}