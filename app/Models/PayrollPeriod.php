<?php

namespace App\Models;

use App\Domain\Tenant\Traits\BelongsToEmployer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    use BelongsToEmployer, HasFactory;

    protected $fillable = [
        'employer_id',
        'year_month',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'year_month' => 'integer',
        ];
    }

    public function runs(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    public function getFormattedPeriodAttribute(): string
    {
        $year = (int) substr((string) $this->year_month, 0, 4);
        $month = (int) substr((string) $this->year_month, 4, 2);
        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return ($monthNames[$month] ?? 'Mes '.$month).' '.$year;
    }
}
