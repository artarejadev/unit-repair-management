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
                ->unsignedInteger('returned_quantity')
                ->default(0)
                ->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('unit_spareparts', function (Blueprint $table): void {
            $table->dropColumn('returned_quantity');
        });
    }
};