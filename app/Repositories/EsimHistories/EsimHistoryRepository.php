<?php

namespace App\Repositories\EsimHistories;

use App\Models\EsimHistory;
use App\Repositories\BaseRepository;

class EsimHistoryRepository extends BaseRepository implements EsimHistoryRepositoryInterface
{
    public function getModel() {
        return EsimHistory::class;
    }

    public function deleteOld($date) {
        return $this->model->whereDate('created_at', '<', $date)->delete();
    }

    public function getByMobileNumber($sdt) {
        return $this->model->where('sdt', $sdt)->first();
    }
}