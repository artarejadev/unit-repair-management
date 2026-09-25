<?php

namespace App\Services\Stock;

use App\Models\Sparepart;
use App\Models\Unit;
use App\Models\UnitSparepart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UnitSparepartService
{
    public function use(
        Unit $unit,
        Sparepart $sparepart,
        int $quantity,
        ?User $user = null,
    ): UnitSparepart {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'Jumlah sparepart harus lebih dari 0.'
            );
        }

        return DB::transaction(function () use (
            $unit,
            $sparepart,
            $quantity,
            $user
        ): UnitSparepart {
            $lockedUnit = Unit::query()
                ->lockForUpdate()
                ->findOrFail($unit->id);

            $lockedSparepart = Sparepart::query()
                ->lockForUpdate()
                ->findOrFail($sparepart->id);

            $reference = 'UNIT:' . $lockedUnit->imei;

            app(StockMovementService::class)->out(
                sparepart: $lockedSparepart,
                quantity: $quantity,
                reference: $reference,
                notes: 'Pemakaian sparepart pada Unit ' . $lockedUnit->imei,
                user: $user,
            );

            return UnitSparepart::create([
                'unit_id' => $lockedUnit->id,
                'sparepart_id' => $lockedSparepart->id,
                'quantity' => $quantity,
            ]);
        });
    }
}