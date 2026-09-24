<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->nullable()
                ->constrained('units')
                ->restrictOnDelete();

            $table->foreignUuid('unit_repair_id')
                ->nullable()
                ->constrained('unit_repairs')
                ->restrictOnDelete();

            $table->foreignUuid('unit_sparepart_id')
                ->nullable()
                ->constrained('unit_spareparts')
                ->restrictOnDelete();

            $table->string('description');

            $table->decimal('quantity', 15, 3)->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);

            $table->timestamps();

            $table->index('invoice_id');
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};