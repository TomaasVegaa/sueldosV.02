<?php

namespace App\Models;

use App\Domain\Tenant\Traits\BelongsToEmployer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEmployeeInput extends Model
{
    use HasFactory, BelongsToEmployer;

    protected $fillable = [
        'employer_id',
        'employment_id',
        'year_month',
        'worked_days',
        'absence_days',
        'overtime_50_hours',
        'overtime_100_hours',
        'custom_informed_values',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year_month' => 'integer',
            'worked_days' => 'integer',
            'absence_days' => 'integer',
            'overtime_50_hours' => 'decimal:2',
            'overtime_100_hours' => 'decimal:2',
            'custom_informed_values' => 'array',
        ];
    }

    public function employment(): BelongsTo
    {
        return $this->belongsTo(Employment::class);
    }
}
