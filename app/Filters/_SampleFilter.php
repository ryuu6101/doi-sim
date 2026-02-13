<?php

namespace App\Filters;

class _SampleFilter extends QueryFilter
{
    protected $filterable = [

    ];
    
    public function filterValue($value) {
        return $this->builder->where('value', 'like', '%' . $value . '%');
    }
}

