<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_units', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->timestamps();

            /*
             * Satu unit hanya boleh masuk satu invoice.
             * Ini lapisan proteksi double billing.
             */
            $table->unique('unit_id');

            $table->unique([
                'invoice_id',
                'unit_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_units');
    }
};