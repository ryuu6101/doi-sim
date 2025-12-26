<?php

namespace App\Http\Controllers\Admins;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FunctionController extends Controller
{
    public function doiSim(Request $request) {
        $sdt = $request->input('sdt');
        $esim = $request->input('esim');
        $ghichu = urlencode($request->input('ghichu'));

        $file = file_get_contents(storage_path('app\Login.txt'));
        $info = [];
        if ($file) $info = explode("\n", $file);

        $username = $info[0] ?? '';
        $password = $info[1] ?? '';
        $cookies = $info[2] ?? '';

        $ch = curl_init();

        try {
            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=NEORemoting".PHP_EOL;
            $postData .= "c0-methodName=getRec".PHP_EOL;
            $postData .= "c0-id=8974_".now()->getPreciseTimestamp(3)."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinanv.chkSMOI('84".$sdt."'%2C'".$esim."')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Origin: http://10.159.22.104",
                    "X-Requested-With: XMLHttpRequest",
                    "Cookie: ".$cookies,
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) 
                                AppleWebKit/537.36 (KHTML, like Gecko) 
                                Chrome/115.0.5790.102 Safari/537.36",
                    "Content-Type: text/plain"
                ]
            ]);

            $response = curl_exec($ch);

            $KQ = $this->getStringData($response, "kqua_chk");
            dd($KQ, $response, $postData, $cookies);
        } catch (Exception $e) {
            
        }
    }

    function getStringData($input, $paramName) {
        $data = $this->beetween($input, "s0['" . $paramName . "']=", ";");
        
        if ($data == null)  return null;
        
        return $this->beetween($input, "var " . $data . "=\"", "\";");
    }

    function beetween($x, $a, $b) {
        $data = explode($a, $x);
        
        if (count($data) < 2) return null;
        
        $newd = explode($b, $data);
        
        if (count($newd) < 2) return null;
        
        return $newd;
    }
}
