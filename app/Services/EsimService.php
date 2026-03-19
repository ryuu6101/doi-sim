<?php

namespace App\Services;

use App\Jobs\DisableSMT;
use Carbon\Carbon;
use Exception;

class EsimService
{
    public function __construct(
        protected CcbsServiceWrapper $ccbsService,
        protected BrandNameService $brandNameService,
    ) {}

    public function success($message = 'THÀNH CÔNG') {
        return ['success' => true, 'message' => $message];
    }

    public function failed($message = 'THẤT BẠI') {
        return ['success' => false, 'message' => $message];
    }

    public function kichHoatGPRS($sdt) {
        try {
            $lay_dvu = $this->ccbsService->layDVu($sdt, 'GPRS');
            if (!str_contains($lay_dvu, 'OK|')) return $this->failed($lay_dvu);
    
            $tach = explode('|', $lay_dvu);
            if ($tach[1] == 1) return $this->success();
    
            $dm_dvu = $this->ccbsService->dmDVu($sdt, 'GPRS');
            if ($dm_dvu == 'THÀNH CÔNG') return $this->success();
    
            return $this->failed($dm_dvu);
        } catch (Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    public function sendWelcomeMessage($sdt, $valid = '', $hotline = '-') {
        try {
            $valid = $valid != '' ? Carbon::createFromFormat('d/m/Y', $valid)->format('d/M/y') : '--/---/--';
            // $hotline = '0918354555';

            $lay_dvu = $this->ccbsService->layDVu($sdt, 'SMT');
            if (!str_contains($lay_dvu, 'OK|')) return $this->failed($lay_dvu);
            
            $tach = explode('|', $lay_dvu);
            if ($tach[1] == 0) {
                $dm_dvu = $this->ccbsService->dmDVu($sdt, 'SMT');
                if ($dm_dvu != 'THÀNH CÔNG') return $this->failed($dm_dvu);
            }
    
            DisableSMT::dispatch($sdt)->delay(now()->addHours(2));
    
            $send_sms = $this->brandNameService->sendWelcomeMessage($sdt, [substr($sdt, -9), $valid, $hotline]);

            if (!$send_sms) return $this->failed('Không gửi được tin nhắn');

            if ($send_sms['RPLY']['ERROR'] == "0") return $this->success();

            return $this->failed($send_sms['RPLY']['ERROR_DESC'] ?? 'Đã xảy ra lỗi!');
        } catch (Exception $e) {
            return $this->failed($e->getMessage());
        }
    }
}