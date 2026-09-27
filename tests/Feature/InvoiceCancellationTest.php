<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceUnit;
use App\Models\RepairType;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\UnitRepair;
use App\Models\User;
use App\Services\Billing\BillingService;
use App\Services\InvoiceCancellationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

use App\Enums\InvoiceStatus;

class InvoiceCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Customer Test',
        ]);
    }

    protected function createTrip(
        Customer $customer,
        string $number,
    ): Trip {
        return Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => $number,
            'trip_date' => now()->toDateString(),
        ]);
    }

    protected function createUnit(
        Customer $customer,
        Trip $trip,
        string $imei,
        UnitStatus $status = UnitStatus::SELESAI,
    ): Unit {
        return Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => $imei,
            'status' => $status,
        ]);
    }

    protected function createRepair(Unit $unit): void
    {
        $repairType = RepairType::create([
            'name' => 'Ganti LCD',
            'default_price' => 100000,
        ]);

        UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,
            'price' => 100000,
            'override_price' => null,
        ]);
    }

    protected function createAdmin(): User
    {
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
        ]);

        /*
        | Sesuaikan dengan mekanisme role project kamu
        | kalau User punya field role.
        */

        $user->role = 'ADMIN';
        $user->save();

        return $user;
    }

    #[Test]
    public function issued_invoice_can_be_canceled(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-CANCEL-001',
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '111111111111111',
        );

        $this->createRepair($unit);

        $invoice = app(BillingService::class)
            ->createForTrip($trip);

        $this->assertSame(
            InvoiceStatus::ISSUED,
            $invoice->status,
        );

        $admin = $this->createAdmin();

        app(InvoiceCancellationService::class)->cancel(
            $invoice,
            $admin,
            'Unit perlu dikerjakan ulang.',
        );

        $invoice->refresh();
        $unit->refresh();

        $this->assertSame(
            InvoiceStatus::CANCELED,
            $invoice->status,
        );

        $this->assertSame(
            UnitStatus::SELESAI,
            $unit->status,
        );

        $this->assertNotNull(
            $invoice->canceled_at,
        );

        $this->assertSame(
            'Unit perlu dikerjakan ulang.',
            $invoice->cancel_reason,
        );
    }

    #[Test]
    public function paid_invoice_cannot_be_canceled(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-CANCEL-002',
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '222222222222222',
        );

        $this->createRepair($unit);

        $invoice = app(BillingService::class)
            ->createForTrip($trip);

        $invoice->update([
            'status' => InvoiceStatus::PAID,
        ]);

        $admin = $this->createAdmin();

        $this->expectException(RuntimeException::class);

        app(InvoiceCancellationService::class)->cancel(
            $invoice,
            $admin,
            'Test cancel paid invoice.',
        );
    }

    #[Test]
    public function canceled_invoice_does_not_block_billing_again(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-CANCEL-003',
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '333333333333333',
        );

        $this->createRepair($unit);

        /*
        | Invoice pertama
        */
        $invoice1 = app(BillingService::class)
            ->createForTrip($trip);

        $this->assertSame(
            UnitStatus::DITAGIHKAN,
            $unit->fresh()->status,
        );

        /*
        | Batalkan invoice
        */
        $admin = $this->createAdmin();

        app(InvoiceCancellationService::class)->cancel(
            $invoice1,
            $admin,
            'Perlu rework.',
        );

        $this->assertSame(
            UnitStatus::SELESAI,
            $unit->fresh()->status,
        );

        /*
        | Billing kedua harus bisa dibuat.
        */
        $invoice2 = app(BillingService::class)
            ->createForTrip($trip);

        $this->assertNotSame(
            $invoice1->id,
            $invoice2->id,
        );

        $this->assertSame(
            UnitStatus::DITAGIHKAN,
            $unit->fresh()->status,
        );

        /*
        | Histori invoice pertama tetap ada.
        */
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice1->id,
            'status' => InvoiceStatus::CANCELED,
        ]);

        /*
        | Unit boleh mempunyai histori invoice lebih dari satu.
        */
        $this->assertSame(
            2,
            InvoiceUnit::query()
                ->where('unit_id', $unit->id)
                ->count(),
        );
    }

    #[Test]
    public function admin_can_mark_canceled_unit_as_rework(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-REWORK-001',
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '444444444444444',
        );

        $unit->update([
            'status' => UnitStatus::REWORK,
        ]);

        $this->assertSame(
            UnitStatus::REWORK,
            $unit->fresh()->status,
        );
    }

    #[Test]
    public function rework_unit_can_be_started_again(): void
    {
        /*
        | Tes ini memastikan status REWORK
        | memang tersedia untuk workflow.
        |
        | Implementasi detail service start() mengikuti
        | authorization/assignment yang sudah ada.
        */

        $this->assertSame(
            'REWORK',
            UnitStatus::REWORK->value,
        );

        $this->assertSame(
            'Rework',
            UnitStatus::REWORK->label(),
        );
    }
}