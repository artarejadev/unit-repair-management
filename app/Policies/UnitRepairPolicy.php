<?php

namespace App\Policies;

use App\Models\UnitRepair;
use App\Models\User;

class UnitRepairPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, UnitRepair $unitRepair): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, UnitRepair $unitRepair): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, UnitRepair $unitRepair): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}