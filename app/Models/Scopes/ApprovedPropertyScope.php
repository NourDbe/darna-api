<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ApprovedPropertyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder
            ->whereNotNull($model->qualifyColumn('approved_at'))
            ->where(
                $model->qualifyColumn('status'),
                'available'
            );
    }
}
