<?php

namespace App\Models;

use App\Enums\UnitStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\User;

class Unit extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'trip_id',
        'imei',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => UnitStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(UnitRepair::class);
    }

    public function spareparts(): HasMany
    {
        return $this->hasMany(UnitSparepart::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(UnitAssignment::class);
    }

    public function invoiceUnit(): HasOne
    {
        return $this->hasOne(InvoiceUnit::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(UnitAssignment::class)
            ->whereNull('ended_at')
            ->latestOfMany();
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->currentAssignment()
            ->where('technician_id', $user->id)
            ->exists();
    }
}