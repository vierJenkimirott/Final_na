<?php

namespace App\Traits;

trait HasRoles
{
    /**
     * Check if the user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->where('name', $role)->isNotEmpty();
    }

    /**
     * Check if the user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    /**
     * Check if the user has all of the given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->count() === count($roles);
    }
}
