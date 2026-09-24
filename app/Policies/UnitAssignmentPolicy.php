<?php

namespace App\Policies;

use App\Models\UnitAssignment;
use App\Models\User;

class UnitAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, UnitAssignment $assignment): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, UnitAssignment $assignment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, UnitAssignment $assignment): bool
    {
        return $user->isAdmin();
    }
}