<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'cuil',
        'first_name',
        'last_name',
    ];

    protected static function booted(): void
    {
        static::creating(function ($worker) {
            if (empty($worker->uuid)) {
                $worker->uuid = (string) Str::uuid();
            }
        });
    }

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->last_name}, {$this->first_name}";
    }

    public function getFormattedCuilAttribute(): string
    {
        $c = preg_replace('/\D/', '', $this->cuil ?? '');
        if (strlen($c) === 11) {
            return substr($c, 0, 2).'-'.substr($c, 2, 8).'-'.substr($c, 10, 1);
        }

        return $this->cuil ?? '';
    }

    public static function isValidCuil(string $cuil): bool
    {
        return Employer::isValidCuit($cuil);
    }
}
