<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $this->hasPermission($user, 'read') && $this->belongsToCurrentJurisdiction($user, $organization);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $this->hasPermission($user, 'update') && $this->belongsToCurrentJurisdiction($user, $organization);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $this->hasPermission($user, 'delete') && $this->belongsToCurrentJurisdiction($user, $organization);
    }

    private function hasPermission(User $user, string $permission): bool
    {
        return $user->currentTeam && $user->hasTeamPermission($user->currentTeam, $permission);
    }

    private function belongsToCurrentJurisdiction(User $user, Organization $organization): bool
    {
        return $user->currentTeam?->jurisdiction_id
            && $organization->jurisdiction_id === $user->currentTeam->jurisdiction_id;
    }
}
