<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    
    use HandlesAuthorization;
    
    public function __construct()
    {
        //
    }
    
    public function customer(User $user)
    {
        return $user->hasRole('customer');
    }
}
