<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? null : false;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() && ! $model->isAdmin();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return false;
    }

    public function suspend(User $user, User $model): bool
    {
        return $user->isAdmin()
            && $model->role === 'user'
            && ! $model->is($user)
            && ! $model->isSuspended();
    }

    public function reactivate(User $user, User $model): bool
    {
        return $user->isAdmin()
            && $model->role === 'user'
            && ! $model->is($user)
            && $model->isSuspended();
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, User $model): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }
}
