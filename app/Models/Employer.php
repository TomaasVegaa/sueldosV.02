<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'cuit',
        'legal_name',
        'status',
        'lock_version',
    ];

    protected static function booted(): void
    {
        static::creating(function ($employer) {
            if (empty($employer->uuid)) {
                $employer->uuid = (string) Str::uuid();
            }
        });
    }

    public function settingsVersions(): HasMany
    {
        return $this->hasMany(EmployerSettingsVersion::class)->orderByDesc('effective_from');
    }

    public function currentSettings(): HasOne
    {
        $today = now()->toDateString();
        return $this->hasOne(EmployerSettingsVersion::class)
            ->where('effective_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $today);
            })
            ->latestOfMany('effective_from');
    }

    public function workplaces(): HasMany
    {
        return $this->hasMany(Workplace::class);
    }

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_employer_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function getFormattedCuitAttribute(): string
    {
        $c = preg_replace('/\D/', '', $this->cuit ?? '');
        if (strlen($c) === 11) {
            return substr($c, 0, 2) . '-' . substr($c, 2, 8) . '-' . substr($c, 10, 1);
        }
        return $this->cuit ?? '';
    }

    /**
     * Validate Argentine CUIT/CUIL with Modulo 11 algorithm.
     */
    public static function isValidCuit(string $cuit): bool
    {
        $clean = preg_replace('/\D/', '', $cuit);
        if (strlen($clean) !== 11) {
            return false;
        }

        $digits = str_split($clean);
        $multipliers = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $digits[$i] * $multipliers[$i];
        }

        $mod = 11 - ($sum % 11);
        $verifier = match ($mod) {
            11 => 0,
            10 => 9,
            default => $mod,
        };

        return (int) $digits[10] === $verifier;
    }
}
