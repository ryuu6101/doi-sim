<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class EsimReport extends Model
{
    use Filterable;

    protected $guarded = ['id'];
    protected $casts = [
        'date_time' => 'datetime',
    ];
}
