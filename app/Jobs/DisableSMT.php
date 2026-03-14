<?php

namespace App\Jobs;

use App\Services\CcbsServiceWrapper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DisableSMT implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $sdt)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CcbsServiceWrapper $ccbsService): void
    {
        $lay_dvu = $ccbsService->layDVu($this->sdt, 'SMT');
        $tach = explode('|', $lay_dvu);

        if (count($tach) < 2 || $tach[1] == 0) return;

        $dm_vu = $ccbsService->dmDVu($this->sdt, 'SMT');
    }
}
