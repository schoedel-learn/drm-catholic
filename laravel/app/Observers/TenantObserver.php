<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TenantObserver
{
    /**
     * Handle the Model "creating" event.
     */
    public function creating(Model $model): void
    {
        if (Auth::hasUser() && Auth::user()->currentTeam) {
            $jurisdictionId = Auth::user()->currentTeam->jurisdiction_id;

            if ($jurisdictionId) {
                // Determine the column to set
                // For Contact, we set 'owner_diocese_id' to the current tenant
                // For Parish, we set 'diocese_id'
                if ($model instanceof \App\Models\Organization) {
                    // For Organization, we set 'jurisdiction_id'
                    if (!$model->jurisdiction_id) {
                        $model->jurisdiction_id = $jurisdictionId;
                    }
                } elseif ($model instanceof \App\Models\Contact) {
                    if (!$model->owner_diocese_id) {
                        $model->owner_diocese_id = $jurisdictionId;
                    }
                } else { // This 'else' will catch Parish, Team, and other models that use 'diocese_id'
                    if (!$model->diocese_id) {
                        $model->diocese_id = $jurisdictionId;
                    }
                }
            }
        }
    }
}
