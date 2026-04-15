<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Http;

class CcbsServiceHttp
{
    protected $thongbao = [
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

    protected $info = [];
    protected $username = "";
    protected $password = "";
    protected $cookies = "";

    public function __construct() {
        $loginFile = storage_path('app\Login.txt');
        if (file_exists($loginFile)) {
            $file = file_get_contents($loginFile);
            $this->info = explode("\n", $file);
            $this->username = $this->info[0] ?? "";
            $this->password = $this->info[1] ?? "";
            $this->cookies = $this->info[2] ?? "";
        }
    }

    public function timestamp() {
        return now()->getPreciseTimestamp(3);
    }

    public function client() {
        return Http::withHeaders([
            "Origin" => "http://10.159.22.104",
            "X-Requested-With" => "XMLHttpRequest",
            "Referer" => "http://10.159.22.104/ccbs/main",
            "Cookie" => $this->cookies,
            "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) ".
                            "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.5790.102 Safari/537.36",
        ])->withOptions([
            'verify' => false,
            'allow_redirects' => false,
            'curl' => [CURLOPT_DNS_CACHE_TIMEOUT => 3600]
        ])->timeout(10)->connectTimeout(5);
    }

    public function ccbsLogin($username = "", $password = "") {
        if ($username == "") $username = $this->username;
        if ($password == "") $password = $this->password;

        $data = [
            'status' => 0,
            'message' => 'Đã xảy ra lỗi!',
        ];

        try {
            $c = substr($password, -6);
            $np = substr($password, 0, strlen($password) - 6);

            $postData = [
                '1iutlomLork' => 'gjsot5pl%7Btizout',
                '1pl%7Btizout' => 'tku4ysgxz%7Bo4rg%7Fu%7Bz4ykz%5BykxVgxgskzkx.%2F',
                'username' => $username,
                'password' => $np,
                'options' => $c,
            ];

            $headers = ['Referer' => 'http://10.159.22.104/ccbs/main?1iutlomLork=|otgiuxk5juoyosey', 'Cookie' => null];

            $url = "http://10.159.22.104/ccbs/main";

            $response = $this->client()->replaceHeaders($headers)->asForm()->post($url, $postData);
            $result = $response->json();

            if ($result === false) {
                $data['message'] = "Không có quyền truy cập";
            } elseif ($result == 0) {
                $cookies_list = $response->cookies();
                $cookies_arr = [];
                foreach ($cookies_list as $cookie) {
                    $cookies_arr[] = $cookie->getName().'='.$cookie->getValue();
                }
                $datacookie = implode('; ', ($cookies_arr));
                
                $this->getImage($datacookie);

                $this->username = $username;
                $this->password = $password;
                $this->cookies = $datacookie;
                
                file_put_contents(storage_path("app\Login.txt"), $username . "\n" . $password . "\n" . $datacookie . "\n10.155.156.56");

                $data['status'] = 200;
                $data['message'] = "Đăng nhập thành công";
                $data['cookies'] = $datacookie;
            } else {
                $data['message'] = [
                    1 => "Tài khoản không chính xác",
                    2 => "Đăng nhập không thành công. HRM: mã HRM không tồn tại!",
                    4 => "OTP không chính xác",
                ][$result] ?? "Tài khoản không hợp lệ";
            }
        } catch (Exception $e) {
            throw $e;
            $data['message'] = "Không có quyền truy cập";
        }

        return $data;
    }

    public function getImage($cookies) {
        try {
            $url = "http://10.159.22.104/ccbs/captcha/img.jsp?random=";
            $this->client()->replaceHeaders(['Cookie' => $cookies])->get($url);
        } catch (Exception $e) {}
    }

    public function getStringData($input, $paramName) {
        $data = $this->between($input, "s0['" . $paramName . "']=", ";");
        if ($data == null)  return null;
        return $this->between($input, "var " . $data . "=\"", "\";");
    }

    public function between($str, $start, $end) {
        $pattern = "/" . preg_quote($start, '/') . "(.*?)" . preg_quote($end, '/') . "/s";
        preg_match($pattern, $str, $matches);
        return $matches[1] ?? null; 
    }

    public function getTextData($scriptName, $methodName, $param0) {
        $textData = "callCount=1".PHP_EOL;
        $textData .= "c0-scriptName=".$scriptName.PHP_EOL;
        $textData .= "c0-methodName=".$methodName.PHP_EOL;
        $textData .= "c0-id=8974_".$this->timestamp().PHP_EOL;
        $textData .= "c0-param0=".$param0.PHP_EOL;
        $textData .= "c0-param1=boolean:false".PHP_EOL;
        $textData .= "xml=true".PHP_EOL;

        return $textData;
    }

    public function doiSim($sdt, $esim, $ghichu) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinanv.chkSMOI('$sdt'%2C'$esim')";
            $postData = $this->getTextData('NEORemoting', 'getRec', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $KQ = $this->getStringData($response, "kqua_chk");

            if ($KQ != "0") return $this->thongbao[$KQ] ?? 'Vui lòng đăng nhập lại!';

            try {
                $params0 = "string:neo.cmdv114.vinanv.dsimtb('$sdt'%2C'$esim'%2C0%2C'$ghichu'%2C'{$this->username}'%2C'0')";
                $postData = $this->getTextData('NEORemoting', 'getValue', $param0);

                $url = "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getValue.dwr";

                $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
                $KQ = $this->getStringData($response, "kqua_chk");
                $oked = $this->between($response, "var s0=\"", "\";");

                if ($oked === null) return "Vui lòng đăng nhập lại!";

                return $oked."|vl";
            } catch (Exception $e) {
                return "Lỗi ngoại biên!";
            }
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    function layMaSim($sdt) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;

            $url = "http://10.159.22.104/ccbs/main?";
            $url .= "1iutlomLork=gjsot5pl{tizout&pl{tizout=neo.pttb_new.pttb.layTTThueBao_esim(%27$sdt%27)&_=".$this->timestamp();

            $response = $this->client()->get($url)->body();
            $KQ = explode(',', $response);

            if (count($KQ) < 8) return "Vui lòng đăng nhập lại!";

            $QRCode = $KQ[6];
			$Barcode = $KQ[7];
			$esim = $KQ[5];

            if ($Barcode == "") return "Không lấy được Barcode";
			if ($esim == "1" && $QRCode == "") return "Không lấy được QrCode Esim";
			if ($esim == "0" || $QRCode == "") return "Không có Esim";
			return $QRCode."|".$Barcode;
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    function taiAnh($ma, $bar, $sdt) {
        try {
            $url = "http://10.159.22.104/ccbs/main?";
            $url .= "1iutlomLork=vzzhetk}5zxgi{{ekyos5vnok{5otvnok{ekyos4px~sr&pxXkvuxzZ%7Fvk=1&wxiujk=$ma&hgxiujk=$bar";
            
            return $this->client()->get($url)->body();
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkMSIN($msin) {
        try {
            $param0 = "string:neo.cmdv114.numbertosim.kiemtra_MSIN_khoitao('$msin'%2C'DNG')";
            $postData = $this->getTextData('NEORemoting', 'getRec', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $kqua_chk = $this->getStringData($response, "kqua_chk");
            $ttin_add = $this->getStringData($response, "ttin_add");

            if ($kqua_chk === "1") return "Sim đã gắn cho thuê bao |".$ttin_add;
            if ($kqua_chk === "2") return "Sim đã bị hủy do CAN thuê bao";
            if ($kqua_chk === "3") return "Sim đã bị hủy do đổi SIM";
            if ($kqua_chk === "4") return "Sim chưa được kích hoạt";
            if ($kqua_chk === "5") return "Sim không tồn tại";
            if ($kqua_chk === "0") return "Sim mới";
            return "Vui lòng đăng nhập lại!";
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function layTTThueBaoV4($sdt, $string_data) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinacore_new.layTTThueBao_v4('$sdt'%2C'0')";
            $postData = $this->getTextData('NEORemoting', 'getRec', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $oked = $this->between($response, "var s1=\"", "\";");

            if ($oked == "1") return "Thuê bao bị hủy";
            if ($oked != "0") return "Vui lòng đăng nhập lại!";

            $data = "OK";
            $string_data = is_string($string_data) ? [$string_data] : $string_data;
            foreach ($string_data as $key => $value) {
                $data .= "|".$this->getStringData($response, $value);
            }

            return $data;
        } catch (Exception $e) {
            throw $e;
            return "Lỗi ngoại biên!";
        }
    }

    public function layTTTBao($sdt, $matinh) {        
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinacore.layTTKhTb('$sdt'%2C'$matinh')";
            $postData = $this->getTextData('DataRemoting', 'getRec', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/DataRemoting.getRec.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $ten_tb = $this->getStringData($response, "ten_tb");

            if ($ten_tb == null) return "Vui lòng đăng nhập lại!";
            if ($ten_tb == "") return "Không tìm thấy thuê bao!";
            return html_entity_decode($ten_tb);
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function layDVu($sdt, $dich_vu) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinanv.docDvDky('$sdt')";
            $postData = $this->getTextData('DataRemoting', 'getDoc', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/DataRemoting.getDoc.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $html = $this->between($response, "s0=\"", "\";");

            if ($html == "") return "Vui lòng đăng nhập lại!";

            $dom = new DOMDocument();
            @$dom->loadHTML(str_replace(["\\", "&nbsp;"], ["", " "], $html));
            $xpath = new DOMXPath($dom);

            $dich_vu = is_array($dich_vu) ? $dich_vu : [$dich_vu];
            $values = "OK";

            foreach ($dich_vu as $key => $value) {
                $value = str_pad($value, 4);
                $checkbox = $xpath->query("//input[@type='checkbox' and @value='{$value}']");
                $checked = $checkbox->item(0)->hasAttribute('checked');
                $values .= "|".(int)$checked;
            }

            return $values;
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function dmDVu($sdt, $dvu) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinanv_4G.dmDV('$sdt'%2C'$dvu%2C'%2C''%2C'{$this->username}')";
            $postData = $this->getTextData('NEORemoting', 'getValue', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getValue.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $kqua = $this->between($response, "s0=\"", "\";");

            if ($kqua == 1) return "THÀNH CÔNG";

            $tach = explode("|", $kqua);

            return $tach[2] ?? "THẤT BẠI";
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function layIOC($sdt) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinacore_new.layTTThueBao_v5('$sdt'%2C'0')";
            $postData = $this->getTextData('NEORemoting', 'getRec', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getRec.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $goi_di = $this->getStringData($response, "goi_di") ?? -1;
            $goi_den = $this->getStringData($response, "goi_den") ?? -1;

            return $goi_di."|".$goi_den;
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function catmoIOC($sdt, $goidi, $goiden) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "neo.cmdv114.vinanv_4G.catmoICOC('0'%2C'$goidi'%2C'$goiden'%2C'$sdt'%2C'%3B'%2C''%2C'{$this->username}')";
            $postData = $this->getTextData('NEORemoting', 'getValue', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/NEORemoting.getValue.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $kqua = $this->between($response, "s0=\"", "\";");

            if ($kqua == '') return "Vui lòng đăng nhập lại";
            if ($kqua == 2) return "THÀNH CÔNG";
            return "THẤT BẠI";
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function layBcEsim($date) {
        try {
            $date = str_replace(['/','-'], '%2F', $date);
            $param0 = "string:neo.cmdv114.vinanv.docBctkSIM_(%22$date%22%2C%22admin_dng%22%2C%22all%22)";
            $postData = $this->getTextData('DataRemoting', 'getDoc', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/DataRemoting.getDoc.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $html = $this->between($response, 'var s0="', '";');

            if (!isset($html) || $html == '') return 'Vui lòng đăng nhập lại!';

            $encodedHtml = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"); 

            $dom = new DOMDocument();
            @$dom->loadHTML($encodedHtml);

            $xpath = new DOMXPath($dom);
            $rows = $xpath->query("//*[contains(@class, 'row0')]");

            $datas = [];
            foreach ($rows as $row) {
                $rowData = [];
                $cells = $row->getElementsByTagName('td');
                
                foreach ($cells as $cell) {
                    $rowData[] = trim($cell->nodeValue);
                }

                $datas[] = $rowData;
            }

            return $datas;
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function layLsTBao($sdt) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinanv.docLsTb('$sdt'%2C'1'%2C'30')";
            $postData = $this->getTextData('DataRemoting', 'getDoc', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/DataRemoting.getDoc.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $html = $this->between($response, 'var s0="', '";');
            // dd($html);

            if (!isset($html) || $html == '') return 'Vui lòng đăng nhập lại!';

            $encodedHtml = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);

            $xpath = new DOMXPath($dom);
            $rows = $xpath->query('//table//tr[position()>1]');

            $datas = [];
            foreach ($rows as $row) {
                $rowData = [];
                $cells = $xpath->query('td//font', $row);
                
                foreach ($cells as $cell) {
                    $rowData[] = trim(($cell->nodeValue));
                }

                $datas[] = $rowData;
            }

            return $datas;
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function layLs3g($sdt) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.tg.doc_lichsu_3g('$sdt')";
            $postData = $this->getTextData('DataRemoting', 'getDoc', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/DataRemoting.getDoc.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $html = $this->between($response, 'var s0="', '";');
            // dd($html);

            if (!isset($html) || $html == '') return 'Vui lòng đăng nhập lại!';

            $encodedHtml = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

            $dom = new DOMDocument();
            @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $xpath = new DOMXPath($dom);
            $rows = $xpath->query('//table[@class="secContent"]//tr[position()>1]');

            $datas = [];
            foreach ($rows as $row) {
                $rowData = [];
                $cells = $row->getElementsByTagName('td');

                foreach ($cells as $cell) {
                    $rowData[] = trim(($cell->nodeValue));
                }

                $datas[] = $rowData;
            }

            return $datas;
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }

    public function docDvuTb($sdt) {
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinanv.docDvuTb('$sdt')";
            $postData = $this->getTextData('DataRemoting', 'getDoc', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/DataRemoting.getDoc.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();
            $html = $this->between($response, 'var s0="', '";');
            // dd($html);

            if (!isset($html) || $html == '') return 'Vui lòng đăng nhập lại!';

            $encodedHtml = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

            $dom = new DOMDocument();
            @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $xpath = new DOMXPath($dom);
            $rows = $xpath->query('//table//tr');

            $datas = [];
            foreach ($rows as $row) {
                $rowData = [];
                $cells = $row->getElementsByTagName('td');

                foreach ($cells as $cell) {
                    $rowData[] = trim(($cell->nodeValue));
                }

                $datas[] = $rowData;
            }

            return $datas;
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        } finally {
            curl_close($ch);
        }
    }

    public function layTTKhTb($sdt, $matinh, $string_data) {        
        try {
            $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;
            $param0 = "string:neo.cmdv114.vinacore.layTTKhTb('$sdt'%2C'$matinh')";
            $postData = $this->getTextData('DataRemoting', 'getDoc', $param0);

            $url = "http://10.159.22.104/ccbs/dwr/exec/DataRemoting.getDoc.dwr";

            $response = $this->client()->withBody($postData, 'text/plain')->post($url)->body();

            $oked = $this->between($response, "var s0=", ";");

            if ($oked == 'null') return "Vui lòng đăng nhập lại!";

            $data = "OK";
            $string_data = is_string($string_data) ? [$string_data] : $string_data;
            foreach ($string_data as $key => $value) {
                $data .= "|".html_entity_decode($this->getStringData($response, $value));
            }

            return $data;
        } catch (Exception $e) {
            return "Lỗi ngoại biên!";
        }
    }
}