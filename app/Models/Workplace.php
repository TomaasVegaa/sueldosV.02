<?php

namespace App\Models;

use App\Domain\Tenant\Traits\BelongsToEmployer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workplace extends Model
{
    use BelongsToEmployer, HasFactory;

    protected $fillable = [
        'employer_id',
        'code',
        'name',
        'locality',
        'address',
    ];
}
