<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitRepair extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'unit_id',
        'repair_type_id',
        'price',
        'override_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'override_price' => 'decimal:2',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function repairType(): BelongsTo
    {
        return $this->belongsTo(RepairType::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getFinalPriceAttribute(): string
    {
        return $this->override_price ?? $this->price;
    }
}