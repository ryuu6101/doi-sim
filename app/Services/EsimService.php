<?php

namespace App\Services;

use App\Jobs\DisableSMT;
use Carbon\Carbon;
use Exception;

class EsimService
{
    protected $thongbaos = [
        "1" => "Đã đặt lệnh đổi SIM cho số thuê bao", 
        "2" => "Đã đặt lệnh đổi SIM cho số thuê bao (có tạo AC cho SIM mới)", 
        "-1000" => "Lỗi khi đổi SIM cho thuê bao  (do khác tỉnh quản lý!!!)", 
        "-1002" => "Lỗi khi đổi SIM thuê bao  TB Blacklist!", 
        "-3010" => "Thuê bao không có trên hệ thống IN-Eric", 
        "4006" => " Thuê bao không có trên hệ thống IN-Comv ", 
    ];

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

    public function doiSim($sdt, $esim, $ghichu) {
        $doi_sim = $this->ccbsService->doiSim($sdt, $esim, $ghichu);

        $message = $doi_sim;

        if (str_contains($doi_sim, "|vl")) {
            $index = str_replace("|vl", "", $doi_sim);
            $message = $this->thongbaos[$index] ?? "Lỗi khi đổi SIM cho thuê bao #404"; 
        }

        if ($doi_sim != "1|vl" && $doi_sim != "2|vl") return $this->failed($message);

        return $this->success($message);
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

    public function sendWelcomeMessage($sdt, $valid = '', $hotline = '') {
        try {
            $valid = $valid != '' ? Carbon::createFromFormat('d/m/Y', $valid)->format('d/M/y') : '--/---/--';
            $hotline = $hotline != '' ? $hotline : '-';
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