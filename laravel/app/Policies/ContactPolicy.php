<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
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
    public function view(User $user, Contact $contact): bool
    {
        return $this->hasPermission($user, 'read') && $this->belongsToCurrentJurisdiction($user, $contact);
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
    public function update(User $user, Contact $contact): bool
    {
        return $this->hasPermission($user, 'update') && $this->belongsToCurrentJurisdiction($user, $contact);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Contact $contact): bool
    {
        return $this->hasPermission($user, 'delete') && $this->belongsToCurrentJurisdiction($user, $contact);
    }

    private function hasPermission(User $user, string $permission): bool
    {
        return $user->currentTeam && $user->hasTeamPermission($user->currentTeam, $permission);
    }

    private function belongsToCurrentJurisdiction(User $user, Contact $contact): bool
    {
        return $user->currentTeam?->jurisdiction_id
            && $contact->owner_jurisdiction_id === $user->currentTeam->jurisdiction_id;
    }
}
