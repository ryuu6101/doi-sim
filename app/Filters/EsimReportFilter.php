<?php

namespace App\Filters;

use Carbon\Carbon;

class EsimReportFilter extends QueryFilter
{
    protected $filterable = [
        'id',
        'service_code',
        'action',
        'sub_type',
        'account',
    ];

    public function filterDateTimeStart($value) {
        return $this->builder->whereDate('date_time', '>=', Carbon::createFromFormat('d/m/Y', $value));
    }

    public function filterDateTimeEnd($value) {
        return $this->builder->whereDate('date_time', '<=', Carbon::createFromFormat('d/m/Y', $value));
    }
    
    public function filterMobileNumber($value) {
        return $this->builder->where('mobile_number', 'like', '%' . $value . '%');
    }
    
    public function filterOldEsim($value) {
        return $this->builder->where('old_esim', 'like', '%' . $value . '%');
    }
    
    public function filterNewEsim($value) {
        return $this->builder->where('new_esim', 'like', '%' . $value . '%');
    }
}

