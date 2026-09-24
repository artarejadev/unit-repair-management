<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_spareparts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->foreignUuid('sparepart_id')
                ->constrained('spareparts')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 3);

            /*
             * Belum dipaksakan sebagai harga billing.
             * Aturan harga sparepart belum dikunci.
             */
            $table->decimal('unit_price', 15, 2)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'unit_id',
                'sparepart_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_spareparts');
    }
};