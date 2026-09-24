<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_repairs', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->foreignUuid('repair_type_id')
                ->constrained('repair_types')
                ->restrictOnDelete();

            /*
             * Snapshot harga default ketika repair
             * dimasukkan ke unit.
             */
            $table->decimal('price', 15, 2);

            /*
             * Harga khusus hanya untuk transaksi ini.
             */
            $table->decimal('override_price', 15, 2)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'unit_id',
                'repair_type_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_repairs');
    }
};