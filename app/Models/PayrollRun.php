<?php

namespace App\Models;

use App\Domain\Tenant\Traits\BelongsToEmployer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayrollRun extends Model
{
    use BelongsToEmployer, HasFactory;

    protected $fillable = [
        'employer_id',
        'payroll_period_id',
        'run_type',
        'run_number',
        'pay_date',
        'base_days',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pay_date' => 'date',
            'run_number' => 'integer',
            'base_days' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id')->withoutGlobalScopes();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PayrollRunVersion::class)->orderByDesc('version_number');
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(PayrollRunVersion::class)->where('status', 'CURRENT')->latestOfMany('version_number');
    }

    public function getRunTypeLabelAttribute(): string
    {
        return match ($this->run_type) {
            'M' => 'Mensual',
            'Q1' => '1.ª Quincena',
            'Q2' => '2.ª Quincena',
            'SAC1' => '1.er SAC (Junio)',
            'SAC2' => '2.do SAC (Diciembre)',
            'VAC' => 'Vacaciones',
            'FIN' => 'Liquidación Final',
            default => $this->run_type,
        };
    }
}
