<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'stock_qty',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'stock_qty' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(UnitSparepart::class);
    }

    public function unitSpareparts()
    {
        return $this->hasMany(UnitSparepart::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}