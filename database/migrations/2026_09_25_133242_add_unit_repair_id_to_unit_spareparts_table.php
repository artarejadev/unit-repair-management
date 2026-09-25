<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_spareparts', function (Blueprint $table): void {
            $table
                ->foreignUuid('unit_repair_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('unit_repairs')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('unit_spareparts', function (Blueprint $table): void {
            $table->dropForeign(['unit_repair_id']);
            $table->dropColumn('unit_repair_id');
        });
    }
};