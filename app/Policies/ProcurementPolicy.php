<?php

namespace App\Policies;

use App\Models\Procurement;
use App\Models\User;

class ProcurementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('procurements.view');
    }

    public function view(User $user, Procurement $procurement): bool
    {
        return $user->can('procurements.view');
    }

    public function create(User $user): bool
    {
        return $user->can('procurements.create');
    }

    public function publish(User $user): bool
    {
        return $user->getAllPermissions()->contains('name', 'procurements.publish');
    }

    public function update(User $user, Procurement $procurement): bool
    {
        return $user->can('procurements.update');
    }

    public function delete(User $user, Procurement $procurement): bool
    {
        return $user->can('procurements.delete');
    }
}
