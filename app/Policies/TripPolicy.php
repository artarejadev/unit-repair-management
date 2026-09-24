<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class TripPolicy
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
        return $user->isAdmin();
    }

    public function delete(User $user, Trip $trip): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        /*
         * Trip yang sudah memiliki unit
         * tidak boleh dihapus.
         */
        if ($trip->units()->exists()) {
            return false;
        }

        /*
         * Trip yang sudah memiliki invoice
         * tidak boleh dihapus.
         */
        if ($trip->invoices()->exists()) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}