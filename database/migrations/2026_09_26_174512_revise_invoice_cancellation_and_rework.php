<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        /*
        |--------------------------------------------------------------------------
        | invoice_units
        |--------------------------------------------------------------------------
        */

        Schema::table('invoice_units', function (Blueprint $table) use ($driver) {
            if ($driver === 'mysql') {
                $unitFk = collect(Schema::getForeignKeys('invoice_units'))
                    ->first(fn (array $fk): bool =>
                        in_array('unit_id', $fk['columns'], true)
                        && $fk['foreign_table'] === 'units'
                    );

                if ($unitFk) {
                    $table->dropForeign($unitFk['name']);
                }
            }

            $indexes = collect(Schema::getIndexes('invoice_units'))->keyBy('name');

            if ($indexes->has('invoice_units_unit_id_unique')) {
                $table->dropUnique('invoice_units_unit_id_unique');
            }

            if (! $indexes->has('invoice_units_invoice_id_unit_id_unique')) {
                $table->unique(
                    ['invoice_id', 'unit_id'],
                    'invoice_units_invoice_id_unit_id_unique'
                );
            }

            if ($driver === 'mysql') {
                $table->foreign('unit_id')
                    ->references('id')
                    ->on('units')
                    ->cascadeOnDelete(); // sesuaikan behavior asli
            }
        });

        /*
        |--------------------------------------------------------------------------
        | invoices
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('invoices', 'canceled_by')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->uuid('canceled_by')
                    ->nullable()
                    ->after('paid_at');

                $table->timestamp('canceled_at')
                    ->nullable()
                    ->after('canceled_by');

                $table->text('cancel_reason')
                    ->nullable()
                    ->after('canceled_at');

                $table->foreign('canceled_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | units.status: enum -> string
        |--------------------------------------------------------------------------
        |
        | Constraint nilai status sudah dijaga oleh PHP Enum UnitStatus
        | di level aplikasi (Eloquent cast). Kolom string bebas driver
        | dan tidak perlu lagi ALTER ENUM/CHECK setiap ada status baru.
        |
        */

        Schema::table('units', function (Blueprint $table) {
            $table->string('status')->default('PENDING')->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['canceled_by']);
            $table->dropColumn(['canceled_by', 'canceled_at', 'cancel_reason']);
        });

        Schema::table('invoice_units', function (Blueprint $table) use ($driver) {
            if ($driver === 'mysql') {
                $unitFk = collect(Schema::getForeignKeys('invoice_units'))
                    ->first(fn (array $fk): bool =>
                        in_array('unit_id', $fk['columns'], true)
                        && $fk['foreign_table'] === 'units'
                    );

                if ($unitFk) {
                    $table->dropForeign($unitFk['name']);
                }
            }

            $table->dropUnique('invoice_units_invoice_id_unit_id_unique');
            $table->unique('unit_id');

            if ($driver === 'mysql') {
                $table->foreign('unit_id')
                    ->references('id')
                    ->on('units')
                    ->cascadeOnDelete();
            }
        });

        Schema::table('units', function (Blueprint $table) {
            $table->enum('status', [
                'PENDING',
                'PROSES',
                'SELESAI',
                'DITAGIHKAN',
                'DIAMBIL',
            ])->default('PENDING')->change();
        });
    }
};