<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        // Jangan izinkan user menghapus dirinya sendiri.
        if ($user->is($model)) {
            return false;
        }

        // Jangan hapus satu-satunya active admin.
        if (
            $model->isAdmin()
            && $model->is_active
            && User::query()
                ->where('role', UserRole::ADMIN->value)
                ->where('is_active', true)
                ->count() <= 1
        ) {
            return false;
        }

        // User teknisi yang sudah punya histori assignment
        // jangan dihapus agar histori tetap aman.
        if ($model->isTechnician() && $model->technicianAssignments()->exists()) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}