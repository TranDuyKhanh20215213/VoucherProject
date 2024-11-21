<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

class AdminPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function changePassword(Admin $admin, Admin $model) 
    { 
        return true;
    }

    public function updateProfile(Admin $admin, Admin $model) 
    { 
        return true;
    }

    public function getProfile(Admin $admin, Admin $model) 
    { 
        return true;
    }

}
