<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Image;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\Concerns\HasRoleAuthorization;

class ImagePolicy
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

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Image $image): bool
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
    public function update(User $user, Image $image): bool
    {
        return $this->canManageContent($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Image $image): bool
    {
        return $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Image $image): bool
    {
        return $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Image $image): bool
    {
        return $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Image $image): bool
    {
        return $this->canDeleteContent($user);
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $this->canDeleteContent($user);
    }
}
