<?php

namespace App\Services;

use App\Jobs\DisableSMT;

class EsimService
{
    public function __construct(
        protected CcbsServiceWrapper $ccbsService,
        protected BrandNameService $brandNameService,
    ) {}

    public function kichHoatGPRS($sdt) {
        $lay_dvu = $this->ccbsService->layDVu($sdt, 'GPRS');
        if (!str_contains($lay_dvu, 'OK|')) return false;

        $tach = explode('|', $lay_dvu);
        if ($tach[1] == 1) return true;

        return $this->ccbsService->dmDVu($sdt, 'GPRS') == 'THÀNH CÔNG';
    }

    public function sendWelcomeMessage($sdt) {
        $valid = today()->format('d/M/y');
        $hotline = '0918354555';

        $lay_dvu = $this->ccbsService->layDVu($sdt, 'SMT');
        if (!str_contains($lay_dvu, 'OK|')) return false;
        
        $tach = explode('|', $lay_dvu);
        if ($tach[1] == 0) {
            $dm_dvu = $this->ccbsService->dmDVu($sdt, 'SMT');
            if ($dm_dvu != 'THÀNH CÔNG') return false;
        }

        DisableSMT::dispatch($sdt)->delay(now()->addHours(2));

        $send_sms = $this->brandNameService->sendWelcomeMessage($sdt, [substr($sdt, -9), $valid, $hotline]);

        return isset($send_sms['RPLY']['ERROR']) && $send_sms['RPLY']['ERROR'] == "0";
    }
}