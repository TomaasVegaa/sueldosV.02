<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerSettingsVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'effective_from',
        'effective_to',
        'employer_type_code',
        'zone_code',
        'activity_code',
        'payment_place',
        'contribution_bank',
        'receipt_template',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'receipt_template' => 'integer',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
