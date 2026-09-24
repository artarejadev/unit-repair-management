<?php

use App\Enums\UnitStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignUuid('trip_id')
                ->constrained('trips')
                ->restrictOnDelete();

            $table->string('imei', 20)->unique();

            $table->enum('status', [
                UnitStatus::PENDING->value,
                UnitStatus::PROSES->value,
                UnitStatus::SELESAI->value,
                UnitStatus::DITAGIHKAN->value,
                UnitStatus::DIAMBIL->value,
            ])->default(UnitStatus::PENDING->value);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'trip_id',
                'status',
            ]);

            $table->index('customer_id');

            $table->foreign([
                'trip_id',
                'customer_id',
            ])
            ->references([
                'id',
                'customer_id',
            ])
            ->on('trips')
            ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};