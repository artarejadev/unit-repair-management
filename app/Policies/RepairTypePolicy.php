<?php

namespace App\Policies;

use App\Models\RepairType;
use App\Models\User;

class RepairTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, RepairType $repairType): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, RepairType $repairType): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, RepairType $repairType): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}