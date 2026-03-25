<?php

namespace App\Repositories\EsimHistories;

use App\Repositories\RepositoryInterface;

interface EsimHistoryRepositoryInterface extends RepositoryInterface
{
    public function deleteOld($date);
    public function getByMobileNumber($sdt);
}