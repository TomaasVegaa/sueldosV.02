<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_version_id',
        'employment_id',
        'file_number',
        'worker_name',
        'worker_cuil',
        'category_code',
        'category_name',
        'hire_date',
        'seniority_years',
        'health_insurer_code',
        'cbu',
        'gross_remunerative',
        'gross_non_remunerative',
        'total_deductions',
        'net_salary',
        'net_salary_words',
        'employer_contributions_total',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'seniority_years' => 'integer',
            'gross_remunerative' => 'decimal:2',
            'gross_non_remunerative' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'employer_contributions_total' => 'decimal:2',
        ];
    }

    public function runVersion(): BelongsTo
    {
        return $this->belongsTo(PayrollRunVersion::class, 'payroll_run_version_id');
    }

    public function employment(): BelongsTo
    {
        return $this->belongsTo(Employment::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayslipLine::class)->orderBy('order');
    }

    public function bases(): HasMany
    {
        return $this->hasMany(PayslipBase::class);
    }
}
