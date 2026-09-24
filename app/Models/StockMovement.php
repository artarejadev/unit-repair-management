<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sparepart_id',
        'unit_id',
        'unit_sparepart_id',
        'user_id',
        'movement_type',
        'quantity',
        'before_qty',
        'after_qty',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'before_qty' => 'decimal:3',
            'after_qty' => 'decimal:3',
        ];
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitSparepart(): BelongsTo
    {
        return $this->belongsTo(UnitSparepart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}