<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceUnit;
use App\Models\RepairType;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\UnitRepair;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->billingService = app(BillingService::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function createCustomer(string $name = 'Customer Test'): Customer
    {
        return Customer::create([
            'name' => $name,
        ]);
    }

    protected function createTrip(
        Customer $customer,
        string $tripNumber = 'TRIP-001',
    ): Trip {
        return Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => $tripNumber,
            'trip_date' => now()->toDateString(),
        ]);
    }

    protected function createUnit(
        Customer $customer,
        Trip $trip,
        string $imei,
        UnitStatus $status = UnitStatus::PENDING,
    ): Unit {
        return Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => $imei,
            'status' => $status,
        ]);
    }

    protected function createRepairType(
        string $name,
        float|int $defaultPrice,
    ): RepairType {
        return RepairType::create([
            'name' => $name,
            'default_price' => $defaultPrice,
        ]);
    }

    protected function createUnitRepair(
        Unit $unit,
        RepairType $repairType,
        float|int $price,
        float|int|null $overridePrice = null,
    ): UnitRepair {
        return UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,
            'price' => $price,
            'override_price' => $overridePrice,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 1
    |--------------------------------------------------------------------------
    | Hanya unit SELESAI yang masuk billing.
    */

    public function only_completed_units_are_billed(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-001',
        );

        $repairType = $this->createRepairType(
            'Ganti LCD',
            50000,
        );

        $unitPending = $this->createUnit(
            $customer,
            $trip,
            '111111111111111',
            UnitStatus::PENDING,
        );

        $unitProses = $this->createUnit(
            $customer,
            $trip,
            '222222222222222',
            UnitStatus::PROSES,
        );

        $unitSelesai = $this->createUnit(
            $customer,
            $trip,
            '333333333333333',
            UnitStatus::SELESAI,
        );

        $this->createUnitRepair(
            $unitPending,
            $repairType,
            50000,
        );

        $this->createUnitRepair(
            $unitProses,
            $repairType,
            50000,
        );

        $this->createUnitRepair(
            $unitSelesai,
            $repairType,
            50000,
        );

        $invoice = $this->billingService->createForTrip($trip);

        $this->assertNotNull($invoice);

        $this->assertDatabaseHas('invoice_units', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unitSelesai->id,
        ]);

        $this->assertDatabaseMissing('invoice_units', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unitPending->id,
        ]);

        $this->assertDatabaseMissing('invoice_units', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unitProses->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 2
    |--------------------------------------------------------------------------
    | Setelah billing, unit berubah menjadi DITAGIHKAN.
    */

    public function billed_unit_changes_to_ditagihkan(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-002',
        );

        $repairType = $this->createRepairType(
            'Ganti Baterai',
            75000,
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '444444444444444',
            UnitStatus::SELESAI,
        );

        $this->createUnitRepair(
            $unit,
            $repairType,
            75000,
        );

        $this->billingService->createForTrip($trip);

        $unit->refresh();

        $this->assertSame(
            UnitStatus::DITAGIHKAN,
            $unit->status,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 3
    |--------------------------------------------------------------------------
    | Harga invoice menggunakan harga transaksi/final_price.
    |--------------------------------------------------------------------------
    */

    public function invoice_uses_unit_repair_final_price(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-003',
        );

        $repairType = $this->createRepairType(
            'Fix Face ID',
            100000,
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '555555555555555',
            UnitStatus::SELESAI,
        );

        /*
        | Harga transaksi disimpan 100.000.
        */
        $unitRepair = $this->createUnitRepair(
            $unit,
            $repairType,
            100000,
        );

        /*
        | Pastikan final_price memang 100.000.
        */
        $this->assertEquals(
            100000,
            $unitRepair->final_price,
        );

        $invoice = $this->billingService->createForTrip($trip);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unit->id,
            'unit_repair_id' => $unitRepair->id,
            'description' => 'Fix Face ID',
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
        ]);

        $this->assertEquals(
            100000,
            (float) $invoice->fresh()->total,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 4
    |--------------------------------------------------------------------------
    | Override price admin harus menjadi harga final billing.
    |--------------------------------------------------------------------------
    */

    public function invoice_uses_override_price_when_available(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-004',
        );

        $repairType = $this->createRepairType(
            'Ganti Kaca',
            150000,
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '666666666666666',
            UnitStatus::SELESAI,
        );

        $unitRepair = $this->createUnitRepair(
            $unit,
            $repairType,
            150000,
            120000,
        );

        $this->assertEquals(
            120000,
            $unitRepair->final_price,
        );

        $invoice = $this->billingService->createForTrip($trip);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unit->id,
            'unit_repair_id' => $unitRepair->id,
            'description' => 'Ganti Kaca',
            'quantity' => 1,
            'unit_price' => 120000,
            'subtotal' => 120000,
        ]);

        $this->assertEquals(
            120000,
            (float) $invoice->fresh()->total,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 5
    |--------------------------------------------------------------------------
    | Satu unit bisa memiliki beberapa repair.
    | Setiap repair menjadi invoice item sendiri.
    |--------------------------------------------------------------------------
    */

    public function multiple_repairs_create_multiple_invoice_items(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-005',
        );

        $lcd = $this->createRepairType(
            'Ganti LCD',
            100000,
        );

        $battery = $this->createRepairType(
            'Ganti Baterai',
            150000,
        );

        $camera = $this->createRepairType(
            'Fix Camera',
            200000,
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '777777777777777',
            UnitStatus::SELESAI,
        );

        $repair1 = $this->createUnitRepair(
            $unit,
            $lcd,
            100000,
        );

        $repair2 = $this->createUnitRepair(
            $unit,
            $battery,
            150000,
        );

        $repair3 = $this->createUnitRepair(
            $unit,
            $camera,
            200000,
        );

        $invoice = $this->billingService->createForTrip($trip);

        $this->assertSame(
            3,
            InvoiceItem::query()
                ->where('invoice_id', $invoice->id)
                ->count(),
        );

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'unit_repair_id' => $repair1->id,
            'unit_price' => 100000,
            'subtotal' => 100000,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'unit_repair_id' => $repair2->id,
            'unit_price' => 150000,
            'subtotal' => 150000,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'unit_repair_id' => $repair3->id,
            'unit_price' => 200000,
            'subtotal' => 200000,
        ]);

        $this->assertEquals(
            450000,
            (float) $invoice->fresh()->total,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 6
    |--------------------------------------------------------------------------
    | Billing beberapa unit dalam satu Trip.
    |--------------------------------------------------------------------------
    */

    public function billing_contains_all_eligible_units_in_same_trip(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-006',
        );

        $repairType = $this->createRepairType(
            'Repair Umum',
            50000,
        );

        $unit1 = $this->createUnit(
            $customer,
            $trip,
            '888888888888881',
            UnitStatus::SELESAI,
        );

        $unit2 = $this->createUnit(
            $customer,
            $trip,
            '888888888888882',
            UnitStatus::SELESAI,
        );

        $unit3 = $this->createUnit(
            $customer,
            $trip,
            '888888888888883',
            UnitStatus::PROSES,
        );

        $this->createUnitRepair(
            $unit1,
            $repairType,
            50000,
        );

        $this->createUnitRepair(
            $unit2,
            $repairType,
            75000,
        );

        $this->createUnitRepair(
            $unit3,
            $repairType,
            100000,
        );

        $invoice = $this->billingService->createForTrip($trip);

        $this->assertSame(
            2,
            InvoiceUnit::query()
                ->where('invoice_id', $invoice->id)
                ->count(),
        );

        $this->assertDatabaseHas('invoice_units', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unit1->id,
        ]);

        $this->assertDatabaseHas('invoice_units', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unit2->id,
        ]);

        $this->assertDatabaseMissing('invoice_units', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unit3->id,
        ]);

        $this->assertEquals(
            125000,
            (float) $invoice->fresh()->total,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 7
    |--------------------------------------------------------------------------
    | Trip harus terisolasi.
    | Billing Trip A tidak boleh mengambil Unit Trip B.
    |--------------------------------------------------------------------------
    */

    public function billing_isolated_between_trips(): void
    {
        $customer = $this->createCustomer();

        $tripA = $this->createTrip(
            $customer,
            'TRIP-A',
        );

        $tripB = $this->createTrip(
            $customer,
            'TRIP-B',
        );

        $repairType = $this->createRepairType(
            'Ganti Speaker',
            80000,
        );

        $unitA = $this->createUnit(
            $customer,
            $tripA,
            '900000000000001',
            UnitStatus::SELESAI,
        );

        $unitB = $this->createUnit(
            $customer,
            $tripB,
            '900000000000002',
            UnitStatus::SELESAI,
        );

        $this->createUnitRepair(
            $unitA,
            $repairType,
            80000,
        );

        $this->createUnitRepair(
            $unitB,
            $repairType,
            90000,
        );

        $invoiceA = $this->billingService->createForTrip($tripA);

        /*
        | Unit Trip A masuk.
        */
        $this->assertDatabaseHas('invoice_units', [
            'invoice_id' => $invoiceA->id,
            'unit_id' => $unitA->id,
        ]);

        /*
        | Unit Trip B tidak boleh masuk invoice Trip A.
        */
        $this->assertDatabaseMissing('invoice_units', [
            'invoice_id' => $invoiceA->id,
            'unit_id' => $unitB->id,
        ]);

        /*
        | Unit B masih SELESAI dan belum ditagihkan.
        */
        $unitB->refresh();

        $this->assertSame(
            UnitStatus::SELESAI,
            $unitB->status,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 8
    |--------------------------------------------------------------------------
    | Unit yang sudah pernah ditagihkan tidak boleh masuk lagi.
    |--------------------------------------------------------------------------
    */

    public function already_billed_unit_cannot_be_billed_again(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-008',
        );

        $repairType = $this->createRepairType(
            'Ganti Connector',
            60000,
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '911111111111111',
            UnitStatus::SELESAI,
        );

        $this->createUnitRepair(
            $unit,
            $repairType,
            60000,
        );

        /*
        | Billing pertama.
        */
        $invoice1 = $this->billingService->createForTrip($trip);

        $this->assertDatabaseHas('invoice_units', [
            'invoice_id' => $invoice1->id,
            'unit_id' => $unit->id,
        ]);

        /*
        | Unit sekarang DITAGIHKAN.
        */
        $unit->refresh();

        $this->assertSame(
            UnitStatus::DITAGIHKAN,
            $unit->status,
        );

        /*
        | Billing kedua harus gagal
        | karena tidak ada unit eligible lagi.
        */
        $this->expectException(RuntimeException::class);

        $this->billingService->createForTrip($trip);

        /*
        | Tidak boleh ada invoice kedua.
        */
        $this->assertSame(
            1,
            Invoice::query()
                ->where('trip_id', $trip->id)
                ->count(),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 9
    |--------------------------------------------------------------------------
    | Bila tidak ada unit yang bisa ditagihkan,
    | service tidak boleh membuat invoice kosong.
    |--------------------------------------------------------------------------
    */

    public function billing_fails_when_trip_has_no_billable_units(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-009',
        );

        $repairType = $this->createRepairType(
            'Repair Test',
            50000,
        );

        /*
        | Hanya ada unit PROSES.
        */
        $unit = $this->createUnit(
            $customer,
            $trip,
            '922222222222222',
            UnitStatus::PROSES,
        );

        $this->createUnitRepair(
            $unit,
            $repairType,
            50000,
        );

        $this->assertSame(
            0,
            Invoice::query()
                ->where('trip_id', $trip->id)
                ->count(),
        );

        $this->expectException(RuntimeException::class);

        $this->billingService->createForTrip($trip);

        /*
        | Pastikan invoice kosong tidak tercipta.
        */
        $this->assertSame(
            0,
            Invoice::query()
                ->where('trip_id', $trip->id)
                ->count(),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 10
    |--------------------------------------------------------------------------
    | Semua invoice context harus menunjuk Customer + Trip yang benar.
    |--------------------------------------------------------------------------
    */

    public function invoice_keeps_correct_customer_and_trip_context(): void
    {
        $customer = $this->createCustomer(
            'Customer Billing Context',
        );

        $trip = $this->createTrip(
            $customer,
            'TRIP-010',
        );

        $repairType = $this->createRepairType(
            'Repair Context',
            95000,
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '933333333333333',
            UnitStatus::SELESAI,
        );

        $this->createUnitRepair(
            $unit,
            $repairType,
            95000,
        );

        $invoice = $this->billingService->createForTrip($trip);

        $this->assertSame(
            $customer->id,
            $invoice->customer_id,
        );

        $this->assertSame(
            $trip->id,
            $invoice->trip_id,
        );

        $this->assertDatabaseHas('invoice_units', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unit->id,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'unit_id' => $unit->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 11
    |--------------------------------------------------------------------------
    | Invoice number harus dibuat dan tidak kosong.
    |--------------------------------------------------------------------------
    */

    public function invoice_number_is_generated(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-011',
        );

        $repairType = $this->createRepairType(
            'Repair Invoice Number',
            70000,
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '944444444444444',
            UnitStatus::SELESAI,
        );

        $this->createUnitRepair(
            $unit,
            $repairType,
            70000,
        );

        $invoice = $this->billingService->createForTrip($trip);

        $this->assertNotEmpty(
            $invoice->invoice_number,
        );

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 12
    |--------------------------------------------------------------------------
    | invoice_items menyimpan quantity = 1 untuk setiap repair.
    |--------------------------------------------------------------------------
    */

    public function each_repair_invoice_item_has_quantity_one(): void
    {
        $customer = $this->createCustomer();

        $trip = $this->createTrip(
            $customer,
            'TRIP-012',
        );

        $repairType = $this->createRepairType(
            'Repair Quantity',
            110000,
        );

        $unit = $this->createUnit(
            $customer,
            $trip,
            '955555555555555',
            UnitStatus::SELESAI,
        );

        $unitRepair = $this->createUnitRepair(
            $unit,
            $repairType,
            110000,
        );

        $invoice = $this->billingService->createForTrip($trip);

        $item = InvoiceItem::query()
            ->where('invoice_id', $invoice->id)
            ->where('unit_repair_id', $unitRepair->id)
            ->first();

        $this->assertNotNull($item);

        $this->assertEquals(
            1,
            $item->quantity,
        );

        $this->assertEquals(
            110000,
            (float) $item->unit_price,
        );

        $this->assertEquals(
            110000,
            (float) $item->subtotal,
        );
    }
}