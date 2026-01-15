<?php

namespace App\Services;

use Exception;

class CcosService
{
    public $cookies = "";
    public $httpHeader;

    public function __construct() {
        $file = storage_path('app\CookiesCcos.txt');
        if (file_exists($file)) {
            $this->cookies = file_get_contents($file);
        }

        $this->httpHeader = [
            "Origin: http://view360ccos.vnpt.vn",
            "X-Requested-With: XMLHttpRequest",
            "Cookie: ".$this->cookies ?? '',
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) 
                        AppleWebKit/537.36 (KHTML, like Gecko) 
                        Chrome/115.0.5790.102 Safari/537.36",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Content-Length: 23",
            "Accept-Encoding: gzip, deflate",
            "Accept-Language: en-US,en;q=0.8",
            "Sec-GPC: 1",
        ];
    }

    public function traCuuMI($sdt) {
        $ch = curl_init();

        try {
            $postData = 'type=GetMI&tb='.$sdt;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://view360ccos.vnpt.vn/Ajax/HandlerThongTinThueBao.ashx",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $this->httpHeader,
            ]);

            $response = curl_exec($ch);
            $decoded = json_decode($response);

            if ($decoded->Code != 1) return $decoded->Message ?? 'Đã xảy ra lỗi!';

            $data = json_decode($decoded->Data);

            $name = $data->LimitUsage[0]->description;
            $limit = $data->LimitUsage[0]->absoluteLimits->bidirVolume / pow(1024, 2);
            $used = $data->AccumulatedData[0]->absoluteAccumulated->bidirVolume / pow(1024, 3);

            return $name."|".round($limit, 1)." GB|".round($used, 1)." GB";
        } catch (Exception $e) {
            throw $e;
            return "Đã xảy ra lỗi!";
        }
    }
}