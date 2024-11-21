<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(Admin $admin)
    {
        return true;
    }

    public function view(Admin $admin, User $user)
    {
        return true;
    }

    public function delete(Admin $admin, User $user)
    {
        return true;
    }

    public function lockOrUnlock(Admin $admin, User $user)
    {
        return true;
    }

    public function changePassword(User $user, User $model) 
    { 
        return true;
    }

    public function updateProfile(User $user, User $model) 
    { 
        return true;
    }

    public function getProfile(User $user, User $model) 
    { 
        return true;
    }

}
