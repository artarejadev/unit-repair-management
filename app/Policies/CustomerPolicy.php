<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Customer $customer): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        /*
         * Customer yang sudah memiliki transaksi
         * tidak boleh dihapus.
         */
        if ($customer->trips()->exists()) {
            return false;
        }

        if ($customer->units()->exists()) {
            return false;
        }

        if ($customer->invoices()->exists()) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}