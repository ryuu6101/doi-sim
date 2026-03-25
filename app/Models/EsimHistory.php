<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class EsimHistory extends Model
{
    use Filterable;

    protected $guarded = ['id'];
}
