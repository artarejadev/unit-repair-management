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
use App\Services\Stock\UnitRepairSparepartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitRepairSparepartTest extends TestCase
{
    use RefreshDatabase;

    public function sparepart_usage_belongs_to_specific_unit_repair_and_reduces_stock(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-urs@test.local',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test',
        ]);

        $trip = Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => 'TRIP-URS-001',
            'trip_date' => now()->toDateString(),
        ]);

        $unit = Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => 'IMEI-URS-001',
        ]);

        $repairType = RepairType::create([
            'name' => 'Ganti LCD',
            'default_price' => 50000,
        ]);

        $unitRepair = UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairType->id,
        ]);

        $sparepart = Sparepart::create([
            'name' => 'LCD iPhone 11',
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

        $sparepart->refresh();
        $usage->refresh();

        $this->assertEquals(9, $sparepart->stock_qty);

        $this->assertEquals(
            $unitRepair->id,
            $usage->unit_repair_id
        );

        $this->assertEquals(
            $unit->id,
            $usage->unit_id
        );

        $this->assertEquals(
            $sparepart->id,
            $usage->sparepart_id
        );

        $this->assertEquals(1, $usage->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'movement_type' => StockMovementType::OUT->value,
            'quantity' => 1,
            'before_qty' => 10,
            'after_qty' => 9,
            'reference' => 'UNIT:' . $unit->imei,
            'user_id' => $admin->id,
        ]);
    }

    public function sparepart_usage_is_not_only_related_to_unit(): void
    {
        $admin = User::create([
            'name' => 'Admin Test 2',
            'email' => 'admin-urs-2@test.local',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Customer Test 2',
        ]);

        $trip = Trip::create([
            'customer_id' => $customer->id,
            'trip_number' => 'TRIP-URS-002',
            'trip_date' => now()->toDateString(),
        ]);

        $unit = Unit::create([
            'customer_id' => $customer->id,
            'trip_id' => $trip->id,
            'imei' => 'IMEI-URS-002',
        ]);

        $repairTypeA = RepairType::create([
            'name' => 'Ganti LCD',
            'default_price' => 50000,
        ]);

        $repairTypeB = RepairType::create([
            'name' => 'Fix Face ID',
            'default_price' => 75000,
        ]);

        $repairA = UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairTypeA->id,
        ]);

        $repairB = UnitRepair::create([
            'unit_id' => $unit->id,
            'repair_type_id' => $repairTypeB->id,
        ]);

        $sparepart = Sparepart::create([
            'name' => 'Face ID Module',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $usageA = app(UnitRepairSparepartService::class)->use(
            unitRepair: $repairA,
            sparepart: $sparepart,
            quantity: 1,
            user: $admin,
        );

        $usageB = app(UnitRepairSparepartService::class)->use(
            unitRepair: $repairB,
            sparepart: $sparepart,
            quantity: 2,
            user: $admin,
        );

        $this->assertNotEquals(
            $usageA->unit_repair_id,
            $usageB->unit_repair_id
        );

        $this->assertEquals(
            $repairA->id,
            $usageA->unit_repair_id
        );

        $this->assertEquals(
            $repairB->id,
            $usageB->unit_repair_id
        );

        $this->assertEquals(7, $sparepart->fresh()->stock_qty);
    }

    public function editing_quantity_up_creates_additional_stock_out(): void
    {
        // setup unit, repair, sparepart stock 10
        // buat usage awal quantity 1

        $this->actingAs($admin);

        app(UnitRepairSparepartService::class)->update(
            unitSparepart: $usage,
            sparepart: $sparepart,
            quantity: 3,
            user: $admin,
        );

        $this->assertEquals(7, $sparepart->fresh()->stock_qty);

        $this->assertDatabaseHas('unit_spareparts', [
            'id' => $usage->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'movement_type' => StockMovementType::OUT->value,
            'quantity' => 2,
        ]);
    }

    public function editing_quantity_down_returns_stock_difference(): void
    {
        // setup stock 10
        // usage awal quantity 3 → stock 7

        $this->actingAs($admin);

        app(UnitRepairSparepartService::class)->update(
            unitSparepart: $usage,
            sparepart: $sparepart,
            quantity: 1,
            user: $admin,
        );

        $this->assertEquals(9, $sparepart->fresh()->stock_qty);

        $this->assertDatabaseHas('unit_spareparts', [
            'id' => $usage->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'movement_type' => StockMovementType::RETURN->value,
            'quantity' => 2,
        ]);
    }

    public function deleting_usage_returns_all_used_stock(): void
    {
        // setup stock 10
        // usage quantity 2 → stock 8

        $this->actingAs($admin);

        app(UnitRepairSparepartService::class)->delete(
            unitSparepart: $usage,
            user: $admin,
        );

        $this->assertEquals(10, $sparepart->fresh()->stock_qty);

        $this->assertDatabaseMissing('unit_spareparts', [
            'id' => $usage->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'movement_type' => StockMovementType::RETURN->value,
            'quantity' => 2,
        ]);
    }
}