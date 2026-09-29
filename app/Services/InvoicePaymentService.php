<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoicePaymentService
{
    public function markAsPaid(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice): Invoice {
            $lockedInvoice = Invoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedInvoice->status !== InvoiceStatus::ISSUED) {
                throw ValidationException::withMessages([
                    'invoice' => 'Hanya invoice berstatus ISSUED yang dapat ditandai sebagai PAID.',
                ]);
            }

            $lockedInvoice->update([
                'status' => InvoiceStatus::PAID,
                'paid_at' => now(),
            ]);

            return $lockedInvoice->fresh();
        });
    }
}