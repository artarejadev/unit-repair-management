<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->string('trip_number', 50)->unique();
            $table->date('trip_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'customer_id',
                'trip_date',
            ]);

            $table->unique([
                'id',
                'customer_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};