<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryScaleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_category_id',
        'effective_from',
        'effective_to',
        'basic_salary',
        'non_taxable_salary',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'basic_salary' => 'decimal:2',
            'non_taxable_salary' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AgreementCategory::class, 'agreement_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
