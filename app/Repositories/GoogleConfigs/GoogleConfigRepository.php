<?php

namespace App\Repositories\GoogleConfigs;

use App\Models\GoogleConfig;
use App\Repositories\BaseRepository;

class GoogleConfigRepository extends BaseRepository implements GoogleConfigRepositoryInterface
{
    public function getModel() {
        return GoogleConfig::class;
    }

    public function getConfig() {
        return $this->first();
    }
}