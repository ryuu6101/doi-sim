<?php

namespace App\Repositories\DataUsages;

use App\Repositories\RepositoryInterface;

interface DataUsageRepositoryInterface extends RepositoryInterface
{
    public function getByMobileNumber($sdt);
    public function deleteOld($sdt);
}