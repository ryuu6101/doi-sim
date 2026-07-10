<?php

namespace App\Repositories\GoogleConfigs;

use App\Repositories\RepositoryInterface;

interface GoogleConfigRepositoryInterface extends RepositoryInterface
{
    public function getConfig();
}