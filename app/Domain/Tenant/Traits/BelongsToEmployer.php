<?php

namespace App\Domain\Tenant\Traits;

use App\Domain\Tenant\EmployerContext;
use App\Domain\Tenant\Scopes\MultiEmployerScope;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToEmployer
{
    public static function bootBelongsToEmployer(): void
    {
        static::addGlobalScope(new MultiEmployerScope);

        static::creating(function ($model) {
            if (empty($model->employer_id)) {
                $activeId = EmployerContext::getActiveEmployerId();
                if ($activeId) {
                    $model->employer_id = $activeId;
                }
            }
        });
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }
}
