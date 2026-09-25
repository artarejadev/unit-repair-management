<?php

namespace App\Services\Stock;

use App\Models\Sparepart;
use App\Models\UnitRepair;
use App\Models\UnitSparepart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UnitRepairSparepartService
{
    public function use(
        UnitRepair $unitRepair,
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
            $unitRepair,
            $sparepart,
            $quantity,
            $user,
        ): UnitSparepart {
            $lockedRepair = UnitRepair::query()
                ->with(['unit', 'repairType'])
                ->lockForUpdate()
                ->findOrFail($unitRepair->id);

            $lockedSparepart = Sparepart::query()
                ->lockForUpdate()
                ->findOrFail($sparepart->id);

            $this->stockOut(
                unitRepair: $lockedRepair,
                sparepart: $lockedSparepart,
                quantity: $quantity,
                user: $user,
            );

            return UnitSparepart::create([
                'unit_repair_id' => $lockedRepair->id,
                'unit_id' => $lockedRepair->unit_id,
                'sparepart_id' => $lockedSparepart->id,
                'quantity' => $quantity,
            ]);
        });
    }

    public function update(
        UnitSparepart $unitSparepart,
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
            $unitSparepart,
            $sparepart,
            $quantity,
            $user,
        ): UnitSparepart {
            $usage = UnitSparepart::query()
                ->with([
                    'unitRepair.unit',
                    'unitRepair.repairType',
                    'sparepart',
                ])
                ->lockForUpdate()
                ->findOrFail($unitSparepart->id);

            $oldSparepart = Sparepart::query()
                ->lockForUpdate()
                ->findOrFail($usage->sparepart_id);

            $newSparepart = Sparepart::query()
                ->lockForUpdate()
                ->findOrFail($sparepart->id);

            $unitRepair = $usage->unitRepair;

            /*
             * Kasus 1:
             * Sparepart tidak berubah, hanya quantity berubah.
             */
            if ($usage->sparepart_id === $newSparepart->id) {
                $difference = $quantity - (int) $usage->quantity;

                if ($difference > 0) {
                    $this->stockOut(
                        unitRepair: $unitRepair,
                        sparepart: $newSparepart,
                        quantity: $difference,
                        user: $user,
                    );
                } elseif ($difference < 0) {
                    $this->stockReturn(
                        unitRepair: $unitRepair,
                        sparepart: $newSparepart,
                        quantity: abs($difference),
                        user: $user,
                    );
                }

                $usage->quantity = $quantity;
                $usage->save();

                return $usage->refresh();
            }

            /*
             * Kasus 2:
             * Sparepart diganti.
             *
             * Old sparepart → RETURN
             * New sparepart → OUT
             */
            $this->stockReturn(
                unitRepair: $unitRepair,
                sparepart: $oldSparepart,
                quantity: (int) $usage->quantity,
                user: $user,
            );

            $this->stockOut(
                unitRepair: $unitRepair,
                sparepart: $newSparepart,
                quantity: $quantity,
                user: $user,
            );

            $usage->sparepart_id = $newSparepart->id;
            $usage->quantity = $quantity;
            $usage->save();

            return $usage->refresh();
        });
    }

    public function delete(
        UnitSparepart $unitSparepart,
        ?User $user = null,
    ): void {
        DB::transaction(function () use (
            $unitSparepart,
            $user,
        ): void {
            $usage = UnitSparepart::query()
                ->with([
                    'unitRepair.unit',
                    'unitRepair.repairType',
                    'sparepart',
                ])
                ->lockForUpdate()
                ->findOrFail($unitSparepart->id);

            $this->stockReturn(
                unitRepair: $usage->unitRepair,
                sparepart: $usage->sparepart,
                quantity: (int) $usage->quantity,
                user: $user,
            );

            $usage->delete();
        });
    }

    private function stockOut(
        UnitRepair $unitRepair,
        Sparepart $sparepart,
        int $quantity,
        ?User $user,
    ): void {
        $imei = $unitRepair->unit->imei;
        $repairName = $unitRepair->repairType->name;

        app(StockMovementService::class)->out(
            sparepart: $sparepart,
            quantity: $quantity,
            reference: 'UNIT:' . $imei,
            notes: 'Pemakaian '
                . $sparepart->name
                . ' untuk repair '
                . $repairName,
            user: $user ?? auth()->user(),
        );
    }

    private function stockReturn(
        UnitRepair $unitRepair,
        Sparepart $sparepart,
        int $quantity,
        ?User $user,
    ): void {
        $imei = $unitRepair->unit->imei;
        $repairName = $unitRepair->repairType->name;

        app(StockMovementService::class)->return(
            sparepart: $sparepart,
            quantity: $quantity,
            reference: 'UNIT:' . $imei,
            notes: 'Pengembalian '
                . $sparepart->name
                . ' dari repair '
                . $repairName,
            user: $user ?? auth()->user(),
        );
    }
}