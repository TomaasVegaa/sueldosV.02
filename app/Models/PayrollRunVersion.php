<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRunVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'version_number',
        'status',
        'parent_version_id',
        'correction_reason',
        'snapshot_sha256',
        'snapshot_json',
        'calculated_by',
        'calculated_at',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'calculated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
