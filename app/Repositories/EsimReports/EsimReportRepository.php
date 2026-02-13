<?php

namespace App\Repositories\EsimReports;

use App\Models\EsimReport;
use App\Repositories\BaseRepository;

class EsimReportRepository extends BaseRepository implements EsimReportRepositoryInterface
{
    public function getModel() {
        return EsimReport::class;
    }
}