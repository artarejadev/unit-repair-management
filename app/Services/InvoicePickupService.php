<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\UnitStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoicePickupService
{
    public function markAsPickedUp(
        Invoice $invoice,
        User $user
    ): Invoice {
        if (! $user->isAdmin()) {
            abort(403);
        }

        return DB::transaction(function () use ($invoice, $user) {

            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::query()
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            if ($lockedInvoice->status !== InvoiceStatus::PAID) {
                throw ValidationException::withMessages([
                    'invoice' => 'Invoice harus berstatus PAID sebelum unit dapat ditandai sudah diambil.',
                ]);
            }

            if ($lockedInvoice->picked_up_at !== null) {
                throw ValidationException::withMessages([
                    'invoice' => 'Invoice ini sudah ditandai sebagai sudah diambil.',
                ]);
            }

            $lockedInvoice->load([
                'invoiceUnits.unit',
            ]);

            foreach ($lockedInvoice->invoiceUnits as $invoiceUnit) {
                $unit = $invoiceUnit->unit;

                if (! $unit) {
                    continue;
                }

                if ($unit->status === UnitStatus::DITAGIHKAN) {
                    $unit->update([
                        'status' => UnitStatus::DIAMBIL,
                    ]);

                    continue;
                }

                if ($unit->status === UnitStatus::DIAMBIL) {
                    continue;
                }

                throw ValidationException::withMessages([
                    'invoice' => "Status unit {$unit->imei} tidak valid untuk proses pengambilan.",
                ]);
            }

            $lockedInvoice->update([
                'picked_up_at' => now(),
                'picked_up_by' => $user->id,
            ]);

            return $lockedInvoice->fresh([
                'customer',
                'trip',
                'pickedUpBy',
                'invoiceUnits.unit',
            ]);
        });
    }
}