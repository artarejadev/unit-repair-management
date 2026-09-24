<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('sparepart_id')
                ->constrained('spareparts')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->nullable()
                ->constrained('units')
                ->restrictOnDelete();

            $table->foreignUuid('unit_sparepart_id')
                ->nullable()
                ->constrained('unit_spareparts')
                ->restrictOnDelete();

            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('movement_type', 30);

            $table->decimal('quantity', 15, 3);
            $table->decimal('before_qty', 15, 3);
            $table->decimal('after_qty', 15, 3);

            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'sparepart_id',
                'movement_type',
            ]);

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};