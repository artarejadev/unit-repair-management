<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\Stock\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function stock_in_increases_stock_and_creates_history(): void
    {
        $admin = User::create([
            'name' => 'Admin Stock Test',
            'email' => 'admin-stock-test@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $sparepart = Sparepart::create([
            'name' => 'LCD iPhone 11',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $movement = app(StockMovementService::class)->in(
            sparepart: $sparepart,
            quantity: 5,
            reference: 'STOCK-IN-001',
            notes: 'Stok awal',
            user: $admin,
        );

        $this->assertSame(
            15,
            $movement->fresh()->after_qty
        );

        $this->assertSame(
            10,
            $movement->fresh()->before_qty
        );

        $this->assertSame(
            5,
            $movement->fresh()->quantity
        );

        $this->assertSame(
            StockMovementType::IN,
            $movement->fresh()->movement_type
        );

        $this->assertSame(
            15,
            $sparepart->fresh()->stock_qty
        );
    }

    public function stock_out_decreases_stock_and_creates_history(): void
    {
        $admin = User::create([
            'name' => 'Admin Stock Out',
            'email' => 'admin-stock-out@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $sparepart = Sparepart::create([
            'name' => 'Battery iPhone 11',
        ]);

        $sparepart->stock_qty = 10;
        $sparepart->save();

        $this->actingAs($admin);

        $movement = app(StockMovementService::class)->out(
            sparepart: $sparepart,
            quantity: 3,
            reference: 'UNIT-001',
            notes: 'Pemakaian unit',
            user: $admin,
        );

        $this->assertSame(
            10,
            $movement->fresh()->before_qty
        );

        $this->assertSame(
            7,
            $movement->fresh()->after_qty
        );

        $this->assertSame(
            StockMovementType::OUT,
            $movement->fresh()->movement_type
        );

        $this->assertSame(
            7,
            $sparepart->fresh()->stock_qty
        );
    }

    public function stock_return_increases_stock_and_creates_history(): void
    {
        $admin = User::create([
            'name' => 'Admin Stock Return',
            'email' => 'admin-stock-return@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $sparepart = Sparepart::create([
            'name' => 'Kaca iPhone 12',
        ]);

        $sparepart->stock_qty = 7;
        $sparepart->save();

        $this->actingAs($admin);

        $movement = app(StockMovementService::class)->return(
            sparepart: $sparepart,
            quantity: 2,
            reference: 'UNIT-001',
            notes: 'Sparepart dikembalikan',
            user: $admin,
        );

        $this->assertSame(
            7,
            $movement->fresh()->before_qty
        );

        $this->assertSame(
            9,
            $movement->fresh()->after_qty
        );

        $this->assertSame(
            StockMovementType::RETURN,
            $movement->fresh()->movement_type
        );

        $this->assertSame(
            9,
            $sparepart->fresh()->stock_qty
        );
    }

    public function stock_history_is_immutable_by_policy(): void
    {
        $admin = User::create([
            'name' => 'Admin Stock Policy',
            'email' => 'admin-stock-policy@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'sparepart_id' => Sparepart::create([
                'name' => 'Face ID Module',
            ])->id,
            'movement_type' => StockMovementType::IN,
            'quantity' => 1,
            'before_qty' => 0,
            'after_qty' => 1,
            'reference' => 'TEST',
            'user_id' => $admin->id,
        ]);

        $this->assertFalse(
            $admin->can('update', $movement)
        );

        $this->assertFalse(
            $admin->can('delete', $movement)
        );
    }
}