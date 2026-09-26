<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class BillingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Trip $trip): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Trip $trip): bool
    {
        return false;
    }

    public function delete(User $user, Trip $trip): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}