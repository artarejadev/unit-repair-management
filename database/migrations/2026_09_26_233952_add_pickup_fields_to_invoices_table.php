<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('picked_up_at')
                ->nullable()
                ->after('paid_at');

            $table->uuid('picked_up_by')
                ->nullable()
                ->after('picked_up_at');

            $table->foreign('picked_up_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['picked_up_by']);

            $table->dropColumn([
                'picked_up_at',
                'picked_up_by',
            ]);
        });
    }
};