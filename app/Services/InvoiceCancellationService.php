<?php

namespace App\Services;

use App\Enums\UnitStatus;
use App\Models\Invoice;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

use App\Enums\InvoiceStatus;

class InvoiceCancellationService
{
    public function cancel(
        Invoice $invoice,
        User $user,
        string $reason,
    ): void {
        if (! $user->isAdmin()) {
            throw new RuntimeException(
                'Hanya ADMIN yang dapat membatalkan invoice.'
            );
        }

        $reason = trim($reason);

        if ($reason === '') {
            throw new RuntimeException(
                'Alasan pembatalan invoice wajib diisi.'
            );
        }

        DB::transaction(function () use (
            $invoice,
            $user,
            $reason,
        ) {
            $invoice = Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->status === InvoiceStatus::PAID) {
                throw new RuntimeException(
                    'Invoice yang sudah PAID tidak dapat dibatalkan.'
                );
            }

            if ($invoice->status === InvoiceStatus::CANCELED) {
                throw new RuntimeException(
                    'Invoice sudah dibatalkan.'
                );
            }

            if ($invoice->status !== InvoiceStatus::ISSUED) {
                throw new RuntimeException(
                    'Hanya invoice ISSUED yang dapat dibatalkan.'
                );
            }

            $unitIds = $invoice->invoiceUnits()
                ->pluck('unit_id');

            $units = Unit::query()
                ->whereIn('id', $unitIds)
                ->lockForUpdate()
                ->get();

            foreach ($units as $unit) {
                if ($unit->status !== UnitStatus::DITAGIHKAN) {
                    throw new RuntimeException(
                        "Status Unit {$unit->imei} tidak valid untuk pembatalan invoice."
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Kembalikan semua unit invoice menjadi SELESAI
            |--------------------------------------------------------------------------
            */

            Unit::query()
                ->whereIn('id', $unitIds)
                ->update([
                    'status' => UnitStatus::SELESAI,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Invoice tetap disimpan sebagai histori
            |--------------------------------------------------------------------------
            */

            $invoice->update([
                'status' => InvoiceStatus::CANCELED,
                'canceled_by' => $user->id,
                'canceled_at' => now(),
                'cancel_reason' => $reason,
            ]);
        });
    }
}