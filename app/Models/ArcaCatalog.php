<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArcaCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_type',
        'code',
        'description',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('catalog_type', strtoupper($type));
    }

    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();
        return $query->where(function ($q) use ($today) {
            $q->whereNull('effective_from')->orWhere('effective_from', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->whereNull('effective_to')->orWhere('effective_to', '>=', $today);
        });
    }

    public static function getObrasSociales(): \Illuminate\Database\Eloquent\Collection
    {
        return static::ofType('OBRA_SOCIAL')->orderBy('code')->get();
    }

    public static function getModalidades(): \Illuminate\Database\Eloquent\Collection
    {
        return static::ofType('MODALIDAD')->orderBy('code')->get();
    }

    public static function getSituaciones(): \Illuminate\Database\Eloquent\Collection
    {
        return static::ofType('SIT_REVISTA')->orderBy('code')->get();
    }
}
