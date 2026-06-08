<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::hasUser() && Auth::user()->currentTeam) {
            $jurisdictionId = Auth::user()->currentTeam->jurisdiction_id;
            $column = $this->tenantColumn($model);

            if ($jurisdictionId && $column) {
                $builder->where($model->getTable() . '.' . $column, $jurisdictionId);
            }
        }
    }

    private function tenantColumn(Model $model): ?string
    {
        if ($model instanceof \App\Models\Contact) {
            return 'owner_jurisdiction_id';
        }

        if (Schema::hasColumn($model->getTable(), 'jurisdiction_id')) {
            return 'jurisdiction_id';
        }

        return null;
    }
}
