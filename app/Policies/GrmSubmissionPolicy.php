<?php

namespace App\Policies;

use App\Models\GrmSubmission;
use App\Models\User;

class GrmSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('grm.view');
    }

    public function view(User $user, GrmSubmission $grmSubmission): bool
    {
        return $user->can('grm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('grm.create');
    }

    public function update(User $user, GrmSubmission $grmSubmission): bool
    {
        return $user->can('grm.update');
    }

    public function delete(User $user, GrmSubmission $grmSubmission): bool
    {
        return $user->can('grm.delete');
    }
}
