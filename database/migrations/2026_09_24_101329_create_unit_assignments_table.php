<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->foreignUuid('technician_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('assigned_at');
            $table->timestamp('ended_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'technician_id',
                'ended_at',
            ]);

            $table->index([
                'unit_id',
                'ended_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_assignments');
    }
};