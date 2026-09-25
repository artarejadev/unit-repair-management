<?php

namespace App\Services\Stock;

use App\Enums\StockMovementType;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockMovementService
{
    public function in(
        Sparepart $sparepart,
        int $quantity,
        ?string $reference = null,
        ?string $notes = null,
        ?User $user = null,
    ): StockMovement {
        return $this->move(
            $sparepart,
            StockMovementType::IN,
            $quantity,
            $reference,
            $notes,
            $user
        );
    }

    public function out(
        Sparepart $sparepart,
        int $quantity,
        ?string $reference = null,
        ?string $notes = null,
        ?User $user = null,
    ): StockMovement {
        return $this->move(
            $sparepart,
            StockMovementType::OUT,
            $quantity,
            $reference,
            $notes,
            $user
        );
    }

    public function return(
        Sparepart $sparepart,
        int $quantity,
        ?string $reference = null,
        ?string $notes = null,
        ?User $user = null,
    ): StockMovement {
        return $this->move(
            $sparepart,
            StockMovementType::RETURN,
            $quantity,
            $reference,
            $notes,
            $user
        );
    }

    private function move(
        Sparepart $sparepart,
        StockMovementType $movementType,
        int $quantity,
        ?string $reference,
        ?string $notes,
        ?User $user,
    ): StockMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'Jumlah stok harus lebih dari 0.'
            );
        }

        return DB::transaction(function () use (
            $sparepart,
            $movementType,
            $quantity,
            $reference,
            $notes,
            $user
        ): StockMovement {
            $lockedSparepart = Sparepart::query()
                ->lockForUpdate()
                ->findOrFail($sparepart->id);

            $beforeQty = (int) ($lockedSparepart->stock_qty ?? 0);

            $delta = $movementType->isIncrease()
                ? $quantity
                : -$quantity;

            $afterQty = $beforeQty + $delta;

            $lockedSparepart->stock_qty = $afterQty;
            $lockedSparepart->save();

            return StockMovement::create([
                'sparepart_id' => $lockedSparepart->id,
                'movement_type' => $movementType->value,
                'quantity' => $quantity,
                'before_qty' => $beforeQty,
                'after_qty' => $afterQty,
                'reference' => $reference,
                'user_id' => $user?->id ?? auth()->id(),
                'notes' => $notes,
            ]);
        });
    }
}