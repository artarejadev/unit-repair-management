<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\Customer;
use App\Models\RepairType;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\UnitRepair;
use App\Models\UnitSparepart;
use App\Models\User;
use App\Services\Stock\StockMovementService;
use App\Services\Stock\UnitRepairSparepartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockFinalTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(string $suffix = ''): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-stock-final' . $suffix . '@test.local',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
            'is_active' => true,
        ]);
    }

    private function createCustomer(string $suffix = ''): Customer
    {
        return Customer::create([
            'name' => 'Customer Test' . $suffix,
        ]);
    }

    private function createTrip(
        Customer $customer,
        string $number
    ): Trip {
        return Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => $number,
            'trip_date' => now()->toDateString(),
        ]);
    }

    private function createUnit(
        Customer $customer,
        Trip $trip,
        string $imei
    ): Unit {
        return Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => $imei,
        ]);
    }

    private function createUnitRepair(
        Unit $unit,
        string $name = 'Ganti LCD'
    ): UnitRepair {
        $repairType = RepairType::create([
            'name' => $name,
            'default_price' => 50000,
        ]);

        return UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,
        ]);
    }

    public function stock_in_out_and_return_keep_balance_correct(): void
    {
        $admin = $this->createAdmin('-basic');

        $sparepart = Sparepart::create([
            'name' => 'LCD Test',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        app(StockMovementService::class)->in(
            sparepart: $sparepart,
            quantity: 5,
            reference: 'TEST-IN',
            user: $admin,
        );

        app(StockMovementService::class)->out(
            sparepart: $sparepart,
            quantity: 3,
            reference: 'TEST-OUT',
            user: $admin,
        );

        app(StockMovementService::class)->return(
            sparepart: $sparepart,
            quantity: 2,
            reference: 'TEST-RETURN',
            user: $admin,
        );

        $this->assertEquals(
            14,
            $sparepart->fresh()->stock_qty
        );

        $this->assertDatabaseCount('stock_movements', 3);
    }

    public function partial_return_never_returns_more_than_remaining_quantity(): void
    {
        $admin = $this->createAdmin('-partial');

        $customer = $this->createCustomer('-partial');
        $trip = $this->createTrip($customer, 'TRIP-STOCK-PARTIAL');
        $unit = $this->createUnit(
            $customer,
            $trip,
            'IMEI-STOCK-PARTIAL'
        );

        $unitRepair = $this->createUnitRepair($unit);

        $sparepart = Sparepart::create([
            'name' => 'LCD Partial',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $usage = app(UnitRepairSparepartService::class)->use(
            unitRepair: $unitRepair,
            sparepart: $sparepart,
            quantity: 4,
            user: $admin,
        );

        app(UnitRepairSparepartService::class)->returnStock(
            unitSparepart: $usage,
            quantity: 1,
            user: $admin,
        );

        $usage->refresh();

        $this->assertEquals(4, $usage->quantity);
        $this->assertEquals(1, $usage->returned_quantity);
        $this->assertEquals(3, $usage->remaining_quantity);
        $this->assertEquals(
            7,
            $sparepart->fresh()->stock_qty
        );

        $this->expectException(InvalidArgumentException::class);

        app(UnitRepairSparepartService::class)->returnStock(
            unitSparepart: $usage,
            quantity: 4,
            user: $admin,
        );
    }

    public function editing_quantity_up_only_reduces_stock_by_difference(): void
    {
        $admin = $this->createAdmin('-edit-up');

        $customer = $this->createCustomer('-edit-up');
        $trip = $this->createTrip($customer, 'TRIP-STOCK-UP');
        $unit = $this->createUnit(
            $customer,
            $trip,
            'IMEI-STOCK-UP'
        );

        $unitRepair = $this->createUnitRepair($unit);

        $sparepart = Sparepart::create([
            'name' => 'LCD Edit Up',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $usage = app(UnitRepairSparepartService::class)->use(
            unitRepair: $unitRepair,
            sparepart: $sparepart,
            quantity: 1,
            user: $admin,
        );

        app(UnitRepairSparepartService::class)->update(
            unitSparepart: $usage,
            sparepart: $sparepart,
            quantity: 3,
            user: $admin,
        );

        $this->assertEquals(
            7,
            $sparepart->fresh()->stock_qty
        );

        $this->assertEquals(
            3,
            $usage->fresh()->quantity
        );
    }

    public function editing_quantity_down_returns_only_the_difference(): void
    {
        $admin = $this->createAdmin('-edit-down');

        $customer = $this->createCustomer('-edit-down');
        $trip = $this->createTrip($customer, 'TRIP-STOCK-DOWN');
        $unit = $this->createUnit(
            $customer,
            $trip,
            'IMEI-STOCK-DOWN'
        );

        $unitRepair = $this->createUnitRepair($unit);

        $sparepart = Sparepart::create([
            'name' => 'LCD Edit Down',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $usage = app(UnitRepairSparepartService::class)->use(
            unitRepair: $unitRepair,
            sparepart: $sparepart,
            quantity: 4,
            user: $admin,
        );

        app(UnitRepairSparepartService::class)->update(
            unitSparepart: $usage,
            sparepart: $sparepart,
            quantity: 2,
            user: $admin,
        );

        $this->assertEquals(
            8,
            $sparepart->fresh()->stock_qty
        );

        $this->assertEquals(
            2,
            $usage->fresh()->quantity
        );
    }

    public function deleting_usage_returns_only_remaining_quantity(): void
    {
        $admin = $this->createAdmin('-delete');

        $customer = $this->createCustomer('-delete');
        $trip = $this->createTrip($customer, 'TRIP-STOCK-DELETE');
        $unit = $this->createUnit(
            $customer,
            $trip,
            'IMEI-STOCK-DELETE'
        );

        $unitRepair = $this->createUnitRepair($unit);

        $sparepart = Sparepart::create([
            'name' => 'LCD Delete',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $usage = app(UnitRepairSparepartService::class)->use(
            unitRepair: $unitRepair,
            sparepart: $sparepart,
            quantity: 3,
            user: $admin,
        );

        app(UnitRepairSparepartService::class)->returnStock(
            unitSparepart: $usage,
            quantity: 1,
            user: $admin,
        );

        app(UnitRepairSparepartService::class)->delete(
            unitSparepart: $usage,
            user: $admin,
        );

        $this->assertEquals(
            10,
            $sparepart->fresh()->stock_qty
        );

        $this->assertDatabaseMissing('unit_spareparts', [
            'id' => $usage->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'movement_type' => StockMovementType::RETURN->value,
            'quantity' => 2,
        ]);
    }

    public function changing_sparepart_after_partial_return_is_rejected(): void
    {
        $admin = $this->createAdmin('-change-part');

        $customer = $this->createCustomer('-change-part');
        $trip = $this->createTrip($customer, 'TRIP-STOCK-CHANGE');
        $unit = $this->createUnit(
            $customer,
            $trip,
            'IMEI-STOCK-CHANGE'
        );

        $unitRepair = $this->createUnitRepair($unit);

        $oldSparepart = Sparepart::create([
            'name' => 'LCD Lama',
        ]);

        $newSparepart = Sparepart::create([
            'name' => 'LCD Baru',
        ]);

        $oldSparepart->stock_qty = 10;
        $oldSparepart->save();

        $newSparepart->stock_qty = 10;
        $newSparepart->save();

        $this->actingAs($admin);

        $usage = app(UnitRepairSparepartService::class)->use(
            unitRepair: $unitRepair,
            sparepart: $oldSparepart,
            quantity: 3,
            user: $admin,
        );

        app(UnitRepairSparepartService::class)->returnStock(
            unitSparepart: $usage,
            quantity: 1,
            user: $admin,
        );

        $this->expectException(InvalidArgumentException::class);

        app(UnitRepairSparepartService::class)->update(
            unitSparepart: $usage,
            sparepart: $newSparepart,
            quantity: 2,
            user: $admin,
        );
    }

    public function stock_movement_history_is_never_deleted_during_correction(): void
    {
        $admin = $this->createAdmin('-history');

        $customer = $this->createCustomer('-history');
        $trip = $this->createTrip($customer, 'TRIP-STOCK-HISTORY');
        $unit = $this->createUnit(
            $customer,
            $trip,
            'IMEI-STOCK-HISTORY'
        );

        $unitRepair = $this->createUnitRepair($unit);

        $sparepart = Sparepart::create([
            'name' => 'LCD History',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $usage = app(UnitRepairSparepartService::class)->use(
            unitRepair: $unitRepair,
            sparepart: $sparepart,
            quantity: 2,
            user: $admin,
        );

        app(UnitRepairSparepartService::class)->returnStock(
            unitSparepart: $usage,
            quantity: 1,
            user: $admin,
        );

        $this->assertEquals(
            2,
            StockMovement::query()
                ->where('sparepart_id', $sparepart->id)
                ->count()
        );

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'movement_type' => StockMovementType::OUT->value,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'movement_type' => StockMovementType::RETURN->value,
            'quantity' => 1,
        ]);
    }
}