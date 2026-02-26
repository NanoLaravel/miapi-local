<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Advertisement;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvertisementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_advertisement');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Advertisement $advertisement): bool
    {
        return $user->can('view_advertisement');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_advertisement');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Advertisement $advertisement): bool
    {
        // Admin puede editar cualquier anuncio
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Editor puede editar sus propios anuncios o si tiene el permiso
        if ($user->hasRole('editor')) {
            return $advertisement->user_id === $user->id || $user->can('update_advertisement');
        }
        
        return $user->can('update_advertisement');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Advertisement $advertisement): bool
    {
        // Solo admin puede eliminar anuncios
        return $user->hasRole('admin') || $user->can('delete_advertisement');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Advertisement $advertisement): bool
    {
        return $user->can('restore_advertisement');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Advertisement $advertisement): bool
    {
        return $user->can('force_delete_advertisement');
    }
}
