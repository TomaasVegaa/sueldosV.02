<?php

namespace App\Models;

use App\Domain\Tenant\Traits\BelongsToEmployer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Employment extends Model
{
    use HasFactory, BelongsToEmployer;

    protected $fillable = [
        'uuid',
        'employer_id',
        'worker_id',
        'file_number',
        'hire_date',
        'seniority_date',
        'termination_date',
        'termination_cause',
        'status',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'seniority_date' => 'date',
            'termination_date' => 'date',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($employment) {
            if (empty($employment->uuid)) {
                $employment->uuid = (string) Str::uuid();
            }
        });
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EmploymentVersion::class)->orderByDesc('effective_from');
    }

    public function currentVersion(): HasOne
    {
        $today = now()->toDateString();
        return $this->hasOne(EmploymentVersion::class)
            ->where('effective_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $today);
            })
            ->latestOfMany('effective_from');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(EmploymentVersion::class)->latestOfMany('effective_from');
    }

    /**
     * Get the version effective on a given date (useful for historical payroll runs).
     */
    public function getVersionOn(string $date): ?EmploymentVersion
    {
        return $this->versions()
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }
}
