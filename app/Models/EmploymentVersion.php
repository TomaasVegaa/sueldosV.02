<?php

namespace App\Models;

use App\Domain\Tenant\Traits\BelongsToEmployer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentVersion extends Model
{
    use HasFactory, BelongsToEmployer;

    protected $fillable = [
        'employment_id',
        'employer_id',
        'effective_from',
        'effective_to',
        'agreement_code',
        'category_code',
        'category_name',
        'workday_type',
        'situation_code',
        'contract_mode_code',
        'health_insurer_code',
        'activity_code',
        'zone_code',
        'condition_code',
        'employer_type_code',
        'cbu',
        'agreed_salary',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'workday_type' => 'integer',
            'agreed_salary' => 'decimal:2',
        ];
    }

    public function employment(): BelongsTo
    {
        return $this->belongsTo(Employment::class);
    }

    public function getWorkdayLabelAttribute(): string
    {
        return match ($this->workday_type) {
            1 => 'Jornada Completa',
            2 => '3/4 Jornada',
            3 => 'Media Jornada (1/2)',
            4 => '1/4 Jornada',
            default => 'Personalizada',
        };
    }
}
