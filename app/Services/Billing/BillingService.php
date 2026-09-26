<?php

namespace App\Services\Billing;

use App\Enums\UnitStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceUnit;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BillingService
{
    public function createForTrip(
        Trip $trip,
        ?User $user = null,
    ): Invoice {
        return DB::transaction(function () use (
            $trip,
            $user,
        ): Invoice {
            $user ??= auth()->user();

            $units = Unit::query()
                ->where('trip_id', $trip->id)
                ->where('status', UnitStatus::SELESAI)
                ->whereDoesntHave('invoiceUnit')
                ->with([
                    'repairs.repairType',
                ])
                ->lockForUpdate()
                ->get();

            if ($units->isEmpty()) {
                throw new RuntimeException(
                    'Tidak ada Unit SELESAI yang belum ditagihkan pada Trip ini.'
                );
            }

            $total = 0;

            $invoice = Invoice::create([
                'customer_id' => $trip->customer_id,
                'trip_id' => $trip->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date' => now()->toDateString(),
                'status' => 'ISSUED',
                'total' => 0,
                'issued_at' => now(),
                'paid_at' => null,
                'notes' => null,
            ]);

            foreach ($units as $unit) {
                InvoiceUnit::create([
                    'invoice_id' => $invoice->id,
                    'unit_id' => $unit->id,
                ]);

                foreach ($unit->repairs as $unitRepair) {
                    $price = (float) $unitRepair->final_price;

                    $subtotal = $price;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'unit_id' => $unit->id,
                        'unit_repair_id' => $unitRepair->id,
                        'unit_sparepart_id' => null,
                        'description' => $unitRepair->repairType->name,
                        'quantity' => 1,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $total += $subtotal;
                }

                $unit->status = UnitStatus::DITAGIHKAN;
                $unit->save();
            }

            $invoice->total = $total;
            $invoice->save();

            return $invoice->refresh();
        });
    }

    private function generateInvoiceNumber(): string
    {
        return 'INV-' . now()->format('YmdHisv');
    }
}