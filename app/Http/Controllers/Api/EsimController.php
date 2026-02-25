<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CcbsServiceWrapper;
use Illuminate\Http\Request;

class EsimController extends Controller
{
    public $thongbaos = [
        "1" => "Đã đặt lệnh đổi SIM cho số thuê bao", 
        "2" => "Đã đặt lệnh đổi SIM cho số thuê bao (có tạo AC cho SIM mới)", 
        "-1000" => "Lỗi khi đổi SIM cho thuê bao  (do khác tỉnh quản lý!!!)", 
        "-1002" => "Lỗi khi đổi SIM thuê bao  TB Blacklist!", 
        "-3010" => "Thuê bao không có trên hệ thống IN-Eric", 
        "4006" => " Thuê bao không có trên hệ thống IN-Comv ",
    ];

    public function __construct(
        protected CcbsServiceWrapper $ccbsService,
    ) {}

    public function doiSim(Request $request) {
        $request->validate([
            'sdt' => 'required',
            'esim' => 'required'
        ]);

        $sdt = $request->input('sdt');
        $esim = $request->input('esim');
        $ghichu = urlencode($request->input('ghichu'));

        $data = [];

        $result = $this->ccbsService->doiSim($sdt, $esim, $ghichu);

        $data["status"] = $result == "1|vl" || $result == "2|vl" ? "OK" : "ERR";

        if (str_contains($result, "|vl")) {
            $data["message"] = $this->thongbaos[str_replace("|vl", "", $result)] ?? "Lỗi khi đổi SIM cho thuê bao #404";
        } else {
            $data["message"] = $result;
        }

        return response()->json($data);
    }

    public function layQr(Request $request) {
        $request->validate(['sdt' => 'required']);

        $sdt = $request->input('sdt');

        $data = [];

        $result = $this->ccbsService->layMaSim($sdt);
        $splited = explode("|", $result);

        $data["status"] = "ERR";

        if (count($splited) < 2) {
            $data["message"] = $result;
        } elseif ($splited[0] == '' || $splited[1] == '') {
            $data["message"] = "Thiếu mã QR hoặc BarCode";
        } else {
            $result = $this->ccbsService->taiAnh($splited[0], $splited[1], $sdt);
            $data["status"] = "OK";
            $data["qr_code"] = base64_encode($result);
        }

        return response()->json($data);
    }

    public function layTTTBaoV4(Request $request) {
        $request->validate([
            'sdt' => 'required|min:11',
            'string_data' => 'required'
        ]);

        $sdt = $request->input('sdt');
        $string_data = $request->input('string_data');

        $data = [];

        $result = $this->ccbsService->layTTThueBaoV4($sdt, $string_data);
        $splited = explode("|", $result);

        if (count($splited) < 2) {
            $data["status"] = "ERR";
            $data["message"] = $result;
        } else {
            unset($splited[0]);
            $string_data = is_string($string_data) ? [$string_data] : $string_data;

            $data["status"] = "OK";
            $data["tttbao"] = array_combine($string_data, $splited);
        }
        
        return response()->json($data);
    }
}
