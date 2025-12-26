<?php

namespace App\Http\Controllers\Admins;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function auth(Request $request) {
        $username = $request->username;
        $password = $request->password;

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
                CURLOPT_HEADER => true,
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
            
            // Separate headers and body
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $header_size);
            $body = trim(substr($response, $header_size));

            // Handle response
            if ($body == 0) {
                // Extract cookies from headers
                preg_match_all('/^Set-Cookie:\\s*([^;]*)/mi', $headers, $matches);
                $datacookie = !empty($matches[1]) ? implode('; ', $matches[1]) : '';

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
                ][$body] ?? "Tài khoản không hợp lệ";
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
}
