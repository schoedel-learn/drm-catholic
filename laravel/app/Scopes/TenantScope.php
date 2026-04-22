<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        \Illuminate\Support\Facades\Log::info('TenantScope: apply start for ' . get_class($model));
        if (Auth::hasUser() && Auth::user()->currentTeam) {
            \Illuminate\Support\Facades\Log::info('TenantScope: has user and team');
            $jurisdictionId = Auth::user()->currentTeam->jurisdiction_id;

            // Determine the column to scope by
            // For Contact, it is 'owner_diocese_id' (to see only contacts OWNED by this tenant)
            // For Organization, it is 'jurisdiction_id'
            // For Parish, it is 'diocese_id'
            $column = 'diocese_id'; // Default
            if ($model instanceof \App\Models\Contact) {
                $column = 'owner_diocese_id';
            } elseif ($model instanceof \App\Models\Organization) {
                $column = 'jurisdiction_id';
            }

            if ($jurisdictionId) {
                $builder->where($model->getTable() . '.' . $column, $jurisdictionId);
            }
        }
    }
}
