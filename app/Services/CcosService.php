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
            "Accept-Encoding: gzip, deflate",
            "Accept-Language: en-US,en;q=0.8",
            "Sec-GPC: 1",
        ];
    }

    public function traCuuMI($sdt) {
        $ch = curl_init();

        try {
            $postData = 'type=GetMI&tb='.$sdt;
            $this->httpHeader[] = "Content-Length: ".strlen($postData);

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://view360ccos.vnpt.vn/Ajax/HandlerThongTinThueBao.ashx",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $this->httpHeader,
            ]);

            $response = curl_exec($ch);
            $decoded = json_decode($response);

            if ($decoded?->Code != 1) return $decoded->Message ?? 'Đã xảy ra lỗi!';

            $data = json_decode($decoded->Data);

            $name = $data->LimitUsage[0]->description ?? '-';
            // $limit = ($data->LimitUsage[0]->absoluteLimits->bidirVolume ?? 0) / pow(1024, 1);
            // $used = ($data->AccumulatedData[0]->absoluteAccumulated->bidirVolume ?? 0) / pow(1024, 2);

            // return $name."|".number_format($limit, 1)." MB|".number_format($used, 1)." MB";

            $limit_bytes = ($data->LimitUsage[0]->absoluteLimits->bidirVolume ?? 0) * 1024;
            $limit = $this->dataVolConvert($limit_bytes);

            preg_match_all('/([0-9.]+)\s*([a-zA-Z]+)/', $limit, $matches, PREG_SET_ORDER);
            $unit = $matches[0][2] ?? '';

            $used_bytes = $data->AccumulatedData[0]->absoluteAccumulated->bidirVolume ?? 0;
            $used = $this->dataVolConvert($used_bytes, $unit);

            return $name."|".$limit."|".$used;
        } catch (Exception $e) {
            throw $e;
            return "Đã xảy ra lỗi!";
        }
    }

    public function dataVolConvert($bytes, $unit = "") {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max($bytes, 0);

        if ($unit != "" && in_array($unit, $units)) {
            $pow = array_search($unit, $units);
        } else {
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        }

        $scaledValue = $bytes / pow(1024, $pow);

        return number_format($scaledValue, 1)." ".$units[$pow];
    }

    public function traCuuTTTBao($sdt, $string_data) {
        $ch = curl_init();

        try {
            $postData = 'type=GetInfo&tb='.$sdt;
            $this->httpHeader[] = "Content-Length: ".strlen($postData);

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://view360ccos.vnpt.vn/Ajax/HandlerThongTinThueBao.ashx",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $this->httpHeader,
            ]);

            $response = curl_exec($ch);
            $decoded = json_decode($response, true);
            
            if (isset($decoded['Message'])) return $decoded['Message'];
            
            $data = "OK";

            foreach ($string_data as $key => $value) {
                $data .= "|".($decoded[$value] ?? '');
            }

            return $data;
        } catch (Exception $e) {
            throw $e;
            return "Đã xảy ra lỗi!";
        }
    }
}