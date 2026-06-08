<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
                if ($model instanceof \App\Models\Contact) {
                    if (!$model->owner_jurisdiction_id) {
                        $model->owner_jurisdiction_id = $jurisdictionId;
                    }
                } elseif (Schema::hasColumn($model->getTable(), 'jurisdiction_id')) {
                    if (!$model->jurisdiction_id) {
                        $model->jurisdiction_id = $jurisdictionId;
                    }
                }
            }
        }
    }
}
