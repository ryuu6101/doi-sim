<?php

namespace App\Repositories\DataUsages;

use App\Models\DataUsage;
use App\Repositories\BaseRepository;

class DataUsageRepository extends BaseRepository implements DataUsageRepositoryInterface
{
    public function getModel() {
        return DataUsage::class;
    }

    public function getByMobileNumber($sdt) {
        return $this->model->where('sdt', $sdt)->get();
    }

    public function deleteOld($sdt) {
        $data_usages = $this->model->where('sdt', $sdt)->get();
        $keep_amount = 4;

        if ($data_usages->count() <= $keep_amount) return false;

        $keep_ids = $data_usages->take($keep_amount)->pluck('id');
        $this->model->whereNotIn('id', $keep_ids)->delete();

        return true;
    }
}