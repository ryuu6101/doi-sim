<?php

namespace App\Services;

use DOMXPath;
use Exception;
use DOMDocument;

class CcbsService 
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
    public $username = "";
    public $password = "";
    public $cookies = "";
    public $httpHeader;
    public $qrCodePath;

    public function __construct() {
        $loginFile = storage_path('app\Login.txt');
        if (file_exists($loginFile)) {
            $file = file_get_contents($loginFile);
            $this->info = explode("\n", $file);
            $this->username = $this->info[0] ?? "";
            $this->password = $this->info[1] ?? "";
            $this->cookies = $this->info[2] ?? "";
        }

        $this->httpHeader = [
            "Origin: http://10.159.22.104",
            "X-Requested-With: XMLHttpRequest",
            // "Cookie: ".$this->info[2] ?? '',
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) 
                        AppleWebKit/537.36 (KHTML, like Gecko) 
                        Chrome/115.0.5790.102 Safari/537.36",
            "Content-Type: text/plain"
        ];

        $this->qrCodePath = 'storage\qr_pdf';
        if (!is_dir($this->qrCodePath)) mkdir($this->qrCodePath, 0755, true);
    }

    public function ccbsLogin($username, $password) {
        $data = [
            'status' => 0,
            'message' => 'Đã xảy ra lỗi!',
        ];
        
        $ch = curl_init();

        try {
            // Extract the last 6 characters and the rest
            $c = substr($password, -6);
            $np = substr($password, 0, strlen($password) - 6);
            
            // Prepare POST data
            $postData = "1iutlomLork=gjsot5pl%7Btizout&1pl%7Btizout=tku4ysgxz%7Bo4rg%7Fu%7Bz4ykz%5BykxVgxgskzkx.%2F&username=" . 
                        $username . "&password=" . $np . "&options=" . $c;
            
            // Set cURL options
            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/main",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => [
                    "Origin: http://10.159.22.104",
                    "X-Requested-With: XMLHttpRequest",
                    "Referer: http://10.159.22.104/ccbs/main?1iutlomLork=|otgiuxk5juoyosey",
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) 
                                AppleWebKit/537.36 (KHTML, like Gecko) 
                                Chrome/115.0.5790.102 Safari/537.36",
                    "Content-Type: application/x-www-form-urlencoded"
                ]
            ]);
            
            // Execute request
            $response = curl_exec($ch);
            
            if ($response === false) {
                $data['message'] = "Không có quyền truy cập";
                return $data;
            }

            // Handle response
            if ($response == 0) {
                $cookies_list = curl_getinfo($ch, CURLINFO_COOKIELIST);
                $cookies_arr = [];
                foreach ($cookies_list as $key => $value) {
                    $splited = explode("\t", $value);
                    $cookies_arr[] = $splited[5].'='.$splited[6];
                }
                $datacookie = implode('; ', ($cookies_arr));

                // Call GetImage function (non-blocking)
                $this->getImage($datacookie);

                // Save login info to file
                file_put_contents(storage_path("app\Login.txt"), $username . "\n" . $password . "\n" . $datacookie . "\n10.155.156.56");

                $data['status'] = 200;
                $data['message'] = "Đăng nhập thành công";
                $data['cookies'] = $datacookie;
            } else {
                $data['message'] = [
                    1 => "Tài khoản không chính xác",
                    2 => "Đăng nhập không thành công. HRM: mã HRM không tồn tại!",
                    4 => "OTP không chính xác",
                ][$response] ?? "Tài khoản không hợp lệ";
            }
        } catch (Exception $e) {
            $data['message'] = "Không có quyền truy cập";
        } finally {
            curl_close($ch);
        }

        return $data;
    }

    public function getImage($cookies) {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/captcha/img.jsp?random=",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Cookie: " . $cookies
                ]
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            // Ignore errors
        }
    }

    public function doiSim($sdt, $esim, $ghichu) {
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
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
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
                                $this->username."'%2C'0')".PHP_EOL;
                $postData .= "c0-param1=boolean:false".PHP_EOL;
                $postData .= "xml=true".PHP_EOL;

                curl_setopt_array($ch, [
                    CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getValue.dwr",
                    CURLOPT_POSTFIELDS => $postData,
                ]);

                $response = curl_exec($ch);
                $KQ = $this->getStringData($response, "kqua_chk");
                $oked = $this->between($response, "var s0=\"", "\";");

                if ($oked == null) return "Vui lòng đăng nhập lại!";

                return $oked."|vl";
            } catch (Exception $e) {
                return "Lỗi ngoại biên!";
            }
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        } finally {
            curl_close($ch);
        }
    }

    function getStringData($input, $paramName) {
        $data = $this->between($input, "s0['" . $paramName . "']=", ";");

        if ($data == null)  return null;

        return $this->between($input, "var " . $data . "=\"", "\";");
    }

    function between($x, $a, $b) {
        $data = explode($a, $x);
        
        if (count($data) < 2) return null;
        
        $newd = explode($b, $data[1]);
        
        if (count($newd) < 2) return null;
        
        return $newd[0];
    }

    function layMaSim($sdt) {
        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);
            $getUrl = "http://10.159.22.104/ccbs/main?1iutlomLork=gjsot5pl{tizout&pl{tizout=neo.pttb_new.pttb.layTTThueBao_esim(%2784".
                        $sdt."%27)&_=".$timestamp;

            curl_setopt_array($ch, [
                CURLOPT_URL => $getUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
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
            return "Lỗi ngoại biên!";
        } finally {
            curl_close($ch);
        }
    }

    function taiAnh($ma, $bar, $sdt) {
        $ch = curl_init();

        try {
            $getUrl = "http://10.159.22.104/ccbs/main?1iutlomLork=vzzhetk}5zxgi{{ekyos5vnok{5otvnok{ekyos4px~sr&pxXkvuxzZ%7Fvk=1&wxiujk=".
                        $ma."&hgxiujk=".$bar;
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $getUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
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
        } finally {
            curl_close($ch);
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

    public function checkMSIN($msin) {
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
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            $kqua_chk = $this->getStringData($response, "kqua_chk");
            $ttin_add = $this->getStringData($response, "ttin_add");
            // dd($response, $kqua_chk, $ttin_add);

            if ($kqua_chk === "1") return "Sim đã gắn cho thuê bao |".$ttin_add;
            if ($kqua_chk === "2") return "Sim đã bị hủy do CAN thuê bao";
            if ($kqua_chk === "3") return "Sim đã bị hủy do đổi SIM";
            if ($kqua_chk === "4") return "Sim chưa được kích hoạt";
            if ($kqua_chk === "0") return "Sim mới";
            return "Sim không tồn tại";
        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        } finally {
            curl_close($ch);
        }
    }

    public function layIMEI($sdt) {
        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=NEORemoting".PHP_EOL;
            $postData .= "c0-methodName=getRec".PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinacore_new.layTTThueBao_v4('".$sdt."'%2C'0')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            $so_msin = $this->getStringData($response, "so_msin");
            $ma_tinh = $this->getStringData($response, "ma_tinh");

            if (isset($so_msin) && $so_msin != "") return $so_msin."|".$ma_tinh;
            return "Vui lòng đăng nhập lại!";
        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        } finally {
            curl_close($ch);
        }
    }

    public function layTTTBao($sdt, $matinh) {        
        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=DataRemoting".PHP_EOL;
            $postData .= "c0-methodName=getRec".PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinacore.layTTKhTb('".$sdt."'%2C'".$matinh."')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/DataRemoting.getRec.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            $ten_tb = $this->getStringData($response, "ten_tb");
            // dd($response, $ten_tb);

            if (isset($ten_tb) && $ten_tb != "") return html_entity_decode($ten_tb);
            return "Vui lòng đăng nhập lại!";
        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        } finally {
            curl_close($ch);
        }
    }

    function daoSim($sdt, $old_esim, $new_esim, $ghichu) {
        $layIMEI = $this->layIMEI("84".$sdt);
        $tach = explode('|', $layIMEI);

        if (count($tach) <= 1) return $tach[0] ?? 'Đã xảy ra lỗi!';

        $imei = $tach[0];
        $matinh = $tach[1];

        if ($imei != $old_esim) return "Số IMEI hiện tại không trùng khớp (".$sdt." - ".$imei.")";

        $tttbao = $this->layTTTBao("84".$sdt, $matinh);

        if (strcasecmp($tttbao, 'CÔNG TY CỔ PHẦN CÔNG NGHỆ CNPT') != 0) return $sdt." - ".$imei;

        return $this->doiSim($sdt, $new_esim, $ghichu);
    }

    public function layDVu($sdt, $dich_vu) {
        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=DataRemoting".PHP_EOL;
            $postData .= "c0-methodName=getDoc".PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinanv.docDvDky('".$sdt."')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getDoc.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            $html = $this->between($response, "s0=\"", "\";");
            // dd($html);
            if ($html == "") return "Vui lòng đăng nhập lại!";

            $dom = new DOMDocument();
            @$dom->loadHTML(str_replace("\\","", $html));
            $xpath = new DOMXPath($dom);

            $dich_vu = is_array($dich_vu) ? $dich_vu : [$dich_vu];
            $values = "checked";

            foreach ($dich_vu as $key => $value) {
                $checkbox = $xpath->query("//input[@type='checkbox' and @value='{$value}']");
                $checked = $checkbox->item(0)->hasAttribute('checked');
                $values .= "|".(int)$checked;
            }

            return $values;
        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        } finally {
            curl_close($ch);
        }
    }

    public function dmDVu($sdt, $dvu) {
        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=NEORemoting".PHP_EOL;
            $postData .= "c0-methodName=getValue".PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinanv_4G.dmDV('".$sdt."'%2C'".$dvu."%2C'%2C''%2C'".$this->username."')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getValue.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            $kqua = $this->between($response, "s0=\"", "\";");

            if ($kqua == 1) return "THÀNH CÔNG";
            return "THẤT BẠI";
        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        } finally {
            curl_close($ch);
        }
    }

    public function layIOC($sdt) {
        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=NEORemoting".PHP_EOL;
            $postData .= "c0-methodName=getRec".PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinacore_new.layTTThueBao_v5('".$sdt."'%2C'0')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            $goi_di = $this->getStringData($response, "goi_di") ?? -1;
            $goi_den = $this->getStringData($response, "goi_den") ?? -1;

            return $goi_di."|".$goi_den;
        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        } finally {
            curl_close($ch);
        }
    }

    public function catmoIOC($sdt, $goidi, $goiden) {
        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=NEORemoting".PHP_EOL;
            $postData .= "c0-methodName=getValue".PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinanv_4G.catmoICOC('0'%2C'".$goidi."'%2C'".$goiden."'%2C'".$sdt."'%2C'%3B'%2C''%2C'".
                            $this->username."')".PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getValue.dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            
            $layIOC = $this->layIOC($sdt);
            $tach = explode("|", $layIOC);
            if (count($tach) < 2) return "Vui lòng đăng nhập lại";

            if ($tach[0] == $goidi && $tach[1] == $goiden) return "THÀNH CÔNG";
            return "THẤT BẠI";
        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        } finally {
            curl_close($ch);
        }
    }

    public function test() {
        $ch = curl_init();

        try {
            $timestamp = now()->getPreciseTimestamp(3);

            $scriptName = 'NEORemoting';
            $methodName = 'getRec';

            $postData = "callCount=1".PHP_EOL;
            $postData .= "c0-scriptName=".$scriptName.PHP_EOL;
            $postData .= "c0-methodName=".$methodName.PHP_EOL;
            $postData .= "c0-id=8974_".$timestamp."".PHP_EOL;
            // $postData .= "c0-param0=string:neo.cmdv114.catmo_ioc.checkVSCC(%2284845674221%22%2C%22VNPT%20VSCC%22)".PHP_EOL;
            $postData .= "c0-param0=string:neo.cmdv114.vinacore_new.layTTThueBao_v5('84845674221'%2C'0')".PHP_EOL;
            // $postData .= "c0-param0=string:neo.cmdv114.vinanv_4G.catmoICOC('0'%2C'0'%2C'1'%2C'84845674221'%2C'%3B'%2C''%2C'cuongpp_dng')"
            //              .PHP_EOL;
            $postData .= "c0-param1=boolean:false".PHP_EOL;
            $postData .= "xml=true".PHP_EOL;

            curl_setopt_array($ch, [
                CURLOPT_URL => "http://10.159.22.104/ccbs/dwr/exec/".$scriptName.".".$methodName.".dwr",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => storage_path('app\cookies.txt'),
                CURLOPT_COOKIEFILE => storage_path('app\cookies.txt'),
                CURLOPT_HTTPHEADER => $this->httpHeader
            ]);

            $response = curl_exec($ch);
            dd($response);
        } catch (Exception $e) {
            return "Vui lòng đăng nhập lại!";
        } finally {
            curl_close($ch);
        }
    }
}