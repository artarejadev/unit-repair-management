<?php

use App\Enums\UserRole;
use App\Models\RepairType;
use App\Models\User;

it('allows admin to manage repair types', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
        'is_active' => true,
    ]);

    expect($admin->can('viewAny', RepairType::class))
        ->toBeTrue();

    expect($admin->can('create', RepairType::class))
        ->toBeTrue();

    $repairType = RepairType::create([
        'name' => 'Ganti LCD',
        'default_price' => 50000,
    ]);

    expect($admin->can('view', $repairType))
        ->toBeTrue();

    expect($admin->can('update', $repairType))
        ->toBeTrue();

    expect($admin->can('delete', $repairType))
        ->toBeTrue();
});

it('does not allow technician to manage repair types', function () {
    $technician = User::factory()->create([
        'role' => UserRole::TEKNISI,
        'is_active' => true,
    ]);

    $repairType = RepairType::create([
        'name' => 'Ganti LCD',
        'default_price' => 50000,
    ]);

    expect($technician->can('viewAny', RepairType::class))
        ->toBeFalse();

    expect($technician->can('view', $repairType))
        ->toBeFalse();

    expect($technician->can('create', RepairType::class))
        ->toBeFalse();

    expect($technician->can('update', $repairType))
        ->toBeFalse();

    expect($technician->can('delete', $repairType))
        ->toBeFalse();
});