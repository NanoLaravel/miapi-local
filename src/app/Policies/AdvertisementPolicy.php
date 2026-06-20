<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Advertisement;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Concerns\HasRoleAuthorization;

class AdvertisementPolicy
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

   
    public function view(User $user, Advertisement $advertisement): bool
        {
            return $this->canViewContent($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->canManageContent($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Advertisement $advertisement): bool
    {
      
        return $this->canManageContent($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Advertisement $advertisement): bool
    {
        
        return  $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Advertisement $advertisement): bool
    {
        return $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Advertisement $advertisement): bool
    {
        return $this->canDeleteContent($user);
    }
}
