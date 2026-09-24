<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'default_price',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function unitRepairs(): HasMany
    {
        return $this->hasMany(UnitRepair::class);
    }
}