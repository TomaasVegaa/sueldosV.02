<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'payslip_id',
        'payroll_concept_id',
        'concept_code',
        'concept_name',
        'concept_type',
        'quantity',
        'unit',
        'unit_price',
        'remunerative_amount',
        'non_taxable_amount',
        'deduction_amount',
        'arca_lsd_code',
        'order',
        'print_on_receipt',
        'calculation_trace',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:4',
            'remunerative_amount' => 'decimal:2',
            'non_taxable_amount' => 'decimal:2',
            'deduction_amount' => 'decimal:2',
            'order' => 'integer',
            'print_on_receipt' => 'boolean',
        ];
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function concept(): BelongsTo
    {
        return $this->belongsTo(PayrollConcept::class, 'payroll_concept_id');
    }
}
