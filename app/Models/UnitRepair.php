<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Auth\Access\AuthorizationException;

class UnitRepair extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'unit_id',
        'repair_type_id',
        'override_price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'override_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (UnitRepair $unitRepair): void {
            $repairType = RepairType::query()
                ->findOrFail($unitRepair->repair_type_id);

            $unitRepair->price = $repairType->default_price;
            $unitRepair->override_price = null;
        });

        static::updating(function (UnitRepair $unitRepair): void {
            if (! $unitRepair->isDirty('override_price')) {
                return;
            }

            $user = auth()->user();

            if (! $user || ! $user->isAdmin()) {
                throw new AuthorizationException(
                    'Hanya Admin yang dapat mengubah harga override.'
                );
            }
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function repairType(): BelongsTo
    {
        return $this->belongsTo(RepairType::class);
    }

    public function getFinalPriceAttribute(): float
    {
        return (float) ($this->override_price ?? $this->price);
    }
}