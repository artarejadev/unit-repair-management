<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignUuid('trip_id')
                ->constrained('trips')
                ->restrictOnDelete();

            $table->string('invoice_number', 50)->nullable()->unique();
            $table->date('invoice_date');

            /*
             * Belum dibuat Enum karena status invoice
             * secara detail belum dikunci.
             */
            $table->string('status', 30)->default('DRAFT');

            $table->decimal('total', 15, 2)->default(0);

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'trip_id',
                'status',
            ]);

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
        Schema::dropIfExists('invoices');
    }
};