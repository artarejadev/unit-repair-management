<?php

use App\Models\Invoice;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/admin/invoices/{invoice}/print', function (Invoice $invoice) {
        abort_unless(
            auth()->check() && auth()->user()->isAdmin(),
            403
        );

        $invoice->load([
            'customer',
            'trip',
            'items.unit',
            'items.unitRepair.repairType',
        ]);

        return view('invoices.print', [
            'invoice' => $invoice,
        ]);
    })->name('invoices.print');
});