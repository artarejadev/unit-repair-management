<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\InvoiceStatus;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_ISSUED = 'ISSUED';
    public const STATUS_PAID = 'PAID';
    public const STATUS_CANCELED = 'CANCELED';

    protected $fillable = [
        'customer_id',
        'trip_id',
        'invoice_number',
        'invoice_date',
        'status',
        'total',
        'issued_at',
        'paid_at',
        'canceled_by',
        'canceled_at',
        'cancel_reason',
        'picked_up_at',
        'picked_up_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'status' => InvoiceStatus::class,
            'total' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'canceled_at' => 'datetime',
            'picked_up_at' => 'datetime',
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

    // public function units(): HasMany
    // {
    //     return $this->hasMany(InvoiceUnit::class);
    // }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoiceUnits()
    {
        return $this->hasMany(InvoiceUnit::class);
    }

    public function units()
    {
        return $this->belongsToMany(
            Unit::class,
            'invoice_units',
            'invoice_id',
            'unit_id',
        );
    }

    public function canceledBy()
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }

    public function pickedUpBy()
    {
        return $this->belongsTo(User::class, 'picked_up_by');
    }
}