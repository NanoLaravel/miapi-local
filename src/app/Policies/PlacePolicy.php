<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Place;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Concerns\HasRoleAuthorization;

class PlacePolicy
{
    use HandlesAuthorization;
    use HasRoleAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
    return $this->canViewContent($user);
    }
 
  
    public function view(User $user, Place $place): bool
    {
        return $this->canViewContent($user);
    }

    
    public function create(User $user): bool
    {
        return $this->canManageContent($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Place $place): bool
    {
        return $this->canManageContent($user);
    }

 
    public function delete(User $user, Place $place): bool
    {
        return $this->canDeleteContent($user);
    }

  
    public function deleteAny(User $user): bool
    {
        return $this->canDeleteContent($user);
    }


    public function forceDelete(User $user, Place $place): bool
    {
        return $this->canDeleteContent($user);
    }


    public function forceDeleteAny(User $user): bool
    {
        return $this->canDeleteContent($user);
    }

    public function restore(User $user, Place $place): bool
    {
        return $this->canDeleteContent($user);
    }

   
    public function restoreAny(User $user): bool
    {
        return $this->canDeleteContent($user);
    }

   
    public function replicate(User $user, Place $place): bool
    {
        return $this->canDeleteContent($user);
    }

 
    public function reorder(User $user): bool
    {
        return $this->canDeleteContent($user);
    }
}
