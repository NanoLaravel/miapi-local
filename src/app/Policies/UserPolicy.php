<?php

namespace App\Policies;

use App\Models\User;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Concerns\HasRoleAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
     use HasRoleAuthorization;

    public function viewAny(User $user): bool
    {
       return $this->canManageUsers($user);
    }

  
    public function view(User $user): bool
    {
        return $this->canManageUsers($user);
    }

  
    public function create(User $user): bool
    {
        return $this->canManageUsers($user);
    }

 
    public function update(User $user): bool
    {
        return $this->canManageUsers($user);
    }

  
    
    public function delete(User $user): bool
    {
        return $this->canManageUsers($user);
    }

 
    public function deleteAny(User $user): bool
    {
        return $this->canManageUsers($user);
    }

 
    public function forceDelete(User $user): bool
    {
        return $this->canManageUsers($user);
    }

 
    public function forceDeleteAny(User $user): bool
    {
        return $this->canManageUsers($user);    
    }


    public function restore(User $user): bool
    {
        return $this->canManageUsers($user);
    }

 
    public function restoreAny(User $user): bool
    {
        return $this->canManageUsers($user);
    }

  
    public function replicate(User $user): bool
    {
        return $this->canManageUsers($user);
    }


    public function reorder(User $user): bool
    {
        return $this->canManageUsers($user);
    }
}
