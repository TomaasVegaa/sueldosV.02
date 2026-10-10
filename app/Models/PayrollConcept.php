<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollConcept extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_id',
        'employer_id',
        'code',
        'name',
        'type',
        'formula',
        'default_amount',
        'liquidation_condition',
        'condition_type',
        'calculation_mode',
        'print_on_receipt',
        'print_quantity',
        'print_unit_price',
        'is_automatic',
        'duration_type',
        'arca_lsd_code',
        'order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:4',
            'print_on_receipt' => 'boolean',
            'print_quantity' => 'boolean',
            'print_unit_price' => 'boolean',
            'is_automatic' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeRemunerative(Builder $query): Builder
    {
        return $query->where('type', 'REMUNERATIVO');
    }

    public function scopeNonTaxable(Builder $query): Builder
    {
        return $query->where('type', 'NO_REMUNERATIVO');
    }

    public function scopeDiscount(Builder $query): Builder
    {
        return $query->where('type', 'DESCUENTO');
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('agreement_id')->whereNull('employer_id');
    }

    public function scopeForAgreementOrGlobal(Builder $query, ?int $agreementId = null): Builder
    {
        return $query->where(function ($q) use ($agreementId) {
            $q->whereNull('agreement_id');
            if ($agreementId) {
                $q->orWhere('agreement_id', $agreementId);
            }
        });
    }

    public function getIsGlobalAttribute(): bool
    {
        return is_null($this->agreement_id) && is_null($this->employer_id);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'REMUNERATIVO' => 'Remunerativo (Con aportes)',
            'NO_REMUNERATIVO' => 'No Remunerativo',
            'DESCUENTO' => 'Descuento / Retención',
            default => $this->type,
        };
    }
}
