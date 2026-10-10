<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AgreementCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_id',
        'code',
        'name',
        'condition',
        'status',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function scales(): HasMany
    {
        return $this->hasMany(CategoryScaleVersion::class)->orderByDesc('effective_from');
    }

    public function currentScale(): HasOne
    {
        $today = now()->toDateString();

        return $this->hasOne(CategoryScaleVersion::class)
            ->where('effective_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $today);
            })
            ->latestOfMany('effective_from');
    }

    public function latestScale(): HasOne
    {
        return $this->hasOne(CategoryScaleVersion::class)->latestOfMany('effective_from');
    }

    public function getScaleOn(string $date): ?CategoryScaleVersion
    {
        return $this->scales()
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    public function getConditionLabelAttribute(): string
    {
        return match ($this->condition) {
            'M' => 'Mensual',
            'D' => 'Diario',
            'H' => 'Por Hora',
            default => 'Mensual',
        };
    }
}
