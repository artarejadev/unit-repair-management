<?php

namespace App\Policies;

use App\Models\Sparepart;
use App\Models\User;

class SparepartPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Sparepart $sparepart): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Sparepart $sparepart): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Sparepart $sparepart): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}