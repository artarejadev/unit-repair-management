<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class InvoicePaymentService
{
    public function markAsPaid(
        Invoice $invoice,
        User $user
    ): Invoice {
        if (! $user->isAdmin()) {
            abort(403);
        }

        if ($invoice->status !== InvoiceStatus::ISSUED) {
            throw ValidationException::withMessages([
                'invoice' => 'Hanya invoice ISSUED yang dapat ditandai sudah dibayar.',
            ]);
        }

        $invoice->update([
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);

        return $invoice->refresh();
    }
}