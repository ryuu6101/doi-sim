<?php

namespace App\Http\Controllers\Admins;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EsimController extends Controller
{
    public $thongbao = [
        1 => "Số MSIM mới đang sử dụng !!!", 
        2 => "Số MSIM mới không nằm trong dải SIM dành cho số thuê bao !!!", 
        3 => "Số MSIM mới không nằm trong kho số !!!", 
        4 => "Số MSIM mới chưa khởi tạo AC. Fax yêu cầu về TTHTKH VINAPHONE để đổi SIM!!!", 
        5 => "Số thuê bao không có thông tin ngày kích hoạt!!!", 
        6 => "Sim đã bị hủy do CAN thuê bao!!!", 
        7 => "Sim đã bị hủy do đổi SIM!!!", 
        8 => "Sim chưa được kích hoạt!!!", 
        10 => "Sim không ở trạng thái sẵn sàng sử dụng!!!", 
    ];

    public $info = [];
    public $httpHeader;
    public $qrCodePath;

    public function __construct() {
        $loginFile = storage_path('app\Login.txt');
        if (file_exists($loginFile)) {
            $file = file_get_contents($loginFile);
            $this->info = explode("\n", $file);
        }

        $this->httpHeader = [
            "Origin: http://10.159.22.104",
            "X-Requested-With: XMLHttpRequest",
            "Cookie: ".$this->info[2] ?? '',
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) 
                        AppleWebKit/537.36 (KHTML, like Gecko) 
                        Chrome/115.0.5790.102 Safari/537.36",
            "Content-Type: text/plain"
        ];

        $this->qrCodePath = 'storage\qr_pdf';
        if (!is_dir($this->qrCodePath)) mkdir($this->qrCodePath, 0755, true);
    }

    public function doiSim(Request $request) {
        $sdt = $request->input('sdt');
        $esim = $request->input('esim');
        $ghichu = urlencode($request->input('ghichu'));

        $username = $this->info[0] ?? '';

        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=NEORemoting".PHP_EOL;
            $postData .= "c0-methodName=getRec".PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinanv.chkSMOI('84".$sdt."'%2C'".$esim."')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $this->httpHeader,
            ]);

            $response = curl_exec($ch);
            $KQ = $this->getStringData($response, "kqua_chk");

            if ($KQ != "0") return $this->thongbao[$KQ] ?? 'Vui lòng đăng nhập lại!';

            try {
                $postData = "callCount=1".PHP_EOL;
                $postData .= "c0-scriptName=NEORemoting".PHP_EOL;
                $postData .= "c0-methodName=getValue".PHP_EOL;
                $postData .= "c0-id=8974_".$timestamp.PHP_EOL;
                $postData .= "c0-param0=string:neo.cmdv114.vinanv.dsimtb('84".$sdt."'%2C'".$esim ."'%2C0%2C'".$ghichu."'%2C'".
                                $username."'%2C'0')".PHP_EOL;
                $postData .= "c0-param1=boolean:false".PHP_EOL;
                $postData .= "xml=true".PHP_EOL;

                curl_setopt_array($ch, [
                    CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getValue.dwr",
                    CURLOPT_POSTFIELDS => $postData,
                ]);

                $response = curl_exec($ch);dd($response);
                $KQ = $this->getStringData($response, "kqua_chk");
                $oked = $this->beetween($response, "var s0=\"", "\";");

                if ($oked == null) return "Vui lòng đăng nhập lại!";

                return $oked."|vl";
            } catch (Exception $e) {
                return "Lỗi ngoại biên!";
            }
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
        return "Lỗi ngoại biên!";
    }

    function getStringData($input, $paramName) {
        $data = $this->beetween($input, "s0['" . $paramName . "']=", ";");

        if ($data == null)  return null;
        
        return $this->beetween($input, "var " . $data . "=\"", "\";");
    }

    function beetween($x, $a, $b) {
        $data = explode($a, $x);
        
        if (count($data) < 2) return null;
        
        $newd = explode($b, $data[1]);
        
        if (count($newd) < 2) return null;
        
        return $newd[0];
    }

    function layMaSim(Request $request) {
        $sdt = $request->input('sdt');

        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);
            $getUrl = "http://10.159.22.104/ccbs/main?1iutlomLork=gjsot5pl{tizout&pl{tizout=neo.pttb_new.pttb.layTTThueBao_esim(%2784".
                        $sdt."%27)&_=".$timestamp;

            curl_setopt_array($ch, [
                CURLOPT_URL => $getUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);

            $KQ = explode(',', $response);

            if (count($KQ) < 8) return "Vui lòng đăng nhập lại!";

            $QRCode = $KQ[6];
			$Barcode = $KQ[7];
			$esim = $KQ[5];

            if ($Barcode == "") return "Không lấy được Barcode";
			if ($esim == "1" && $QRCode == "") return "Không lấy được QrCode Esim";
			if ($esim == "0" || $QRCode == "") return " Không có Esim";
			return $QRCode."|".$Barcode;
        } catch (Exception $e) {

        }
        return "Vui lòng đăng nhập lại!";
    }

    function taiAnh(Request $request) {
        $ma = $request->input('ma');
        $bar = $request->input('bar');
        $sdt = $request->input('sdt');

        $ch = curl_init();

        try {
            $getUrl = "http://10.159.22.104/ccbs/main?1iutlomLork=vzzhetk}5zxgi{{ekyos5vnok{5otvnok{ekyos4px~sr&pxXkvuxzZ%7Fvk=1&wxiujk=".
                        $ma."&hgxiujk=".$bar;
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $getUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);

            $file_path = $this->qrCodePath."/".$sdt.".pdf";
            file_put_contents($file_path, $response);
            
            if (file_exists($file_path)) return asset($file_path);

            return false;
            // $this->convertAndCropPdf($response, 1, 993, 1558, $sdt);
        } catch (Exception $e) {
            return false;
        }
    }

    public function convertAndCropPdf($pdfContent, $pageNumber, $width, $height, $sdt) {
        try {
            // Create temporary file for PDF
            $tempPdfPath = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($tempPdfPath, $pdfContent);

            // Use Imagick to convert PDF to image
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($tempPdfPath . '[' . ($pageNumber - 1) . ']');
            $imagick->scaleImage($width, $height);
            $imagick->setImageFormat('jpeg');

            // Crop the QR code region (737, 261, 110x110)
            $imagick->cropImage(110, 110, 737, 261);

            // Save the cropped image
            $outputPath = $this->qrCodePath . '/' . $sdt . '.jpeg';
            $imagick->writeImage($outputPath);
            $imagick->destroy();

            // Clean up temp file
            unlink($tempPdfPath);

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function pdfTest() {
        // Use Imagick to convert PDF to image
        $imagick = new \Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage('test.pdf[0]');
        $imagick->scaleImage(993, 1558);
        $imagick->setImageFormat('jpeg');

        // Crop the QR code region (737, 261, 110x110)
        $imagick->cropImage(110, 110, 737, 261);

        // Save the cropped image
        $outputPath = $this->qrCodePath . '/testqr.jpeg';
        $imagick->writeImage($outputPath);
        $imagick->destroy();
    }

    public function checkMSIN(Request $request) {
        $msin = $request->input('msin');

        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=NEORemoting".PHP_EOL;
            $postData .= "c0-methodName=getRec".PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.numbertosim.kiemtra_MSIN_khoitao('".$msin."'%2C'DNG')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            $KQ = $this->getStringData($response, "ttin_add");

            if ($KQ > 0) return "Sim đã gắn cho thuê bao ".$KQ;
            if ($KQ === 0) return "Sim chưa được sử dụng";
            return "Sim không tồn tại";

        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        }
    }
}
