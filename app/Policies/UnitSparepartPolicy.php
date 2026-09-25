<?php

namespace App\Policies;

use App\Models\UnitSparepart;
use App\Models\User;

class UnitSparepartPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, UnitSparepart $unitSparepart): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, UnitSparepart $unitSparepart): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, UnitSparepart $unitSparepart): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}