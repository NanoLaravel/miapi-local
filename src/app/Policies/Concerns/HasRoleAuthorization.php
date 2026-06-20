<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait HasRoleAuthorization
{
       protected function canViewContent(User $user): bool
    {
        return $user->hasAnyRole([
            'user',
            'editor',
            'admin',
            'super_admin',
        ]);
    }

    protected function canManageContent(User $user): bool
    {
        return $user->hasAnyRole([            
            'editor',
            'admin',
            'super_admin',
        ]);
    }

    protected function canDeleteContent(User $user): bool
    {
        return $user->hasAnyRole([            
            'admin',
            'super_admin',
        ]);
    }

        protected function canManageUsers(User $user): bool
    {
        return $user->hasAnyRole([
            'admin',
            'super_admin',
        ]);
    }
}