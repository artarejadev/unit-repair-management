<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\UnitStatus;

class Trip extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'trip_number',
        'trip_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function billingUnits(): HasMany
    {
        return $this->hasMany(Unit::class)
            ->where('status', UnitStatus::SELESAI)
            ->whereDoesntHave('invoiceUnits', function ($query) {
                $query->whereHas('invoice', function ($invoiceQuery) {
                    $invoiceQuery->where('status', '!=', 'CANCELED');
                });
            });
    }
}