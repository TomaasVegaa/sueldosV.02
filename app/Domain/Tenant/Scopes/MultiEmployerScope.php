<?php

namespace App\Domain\Tenant\Scopes;

use App\Domain\Tenant\EmployerContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MultiEmployerScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $employerId = EmployerContext::getActiveEmployerId();

        if ($employerId !== null) {
            $builder->where($model->qualifyColumn('employer_id'), $employerId);
        }
    }
}
