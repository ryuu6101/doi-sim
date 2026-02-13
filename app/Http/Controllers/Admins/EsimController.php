<?php

namespace App\Http\Controllers\Admins;

use Exception;
use Carbon\Carbon;
use App\Models\EsimReport;
use Illuminate\Http\Request;
use App\Services\CcbsService;
use App\Services\CcosService;
use App\Http\Controllers\Controller;

class EsimController extends Controller
{
    protected $ccbsService;
    protected $ccosService;

    public function __construct(
        CcbsService $ccbsService,
        CcosService $ccosService,
    ) {
        $this->ccbsService = $ccbsService;
        $this->ccosService = $ccosService;
    }

    public function ccbsLogin(Request $request) {
        $username = $request->username;
        $password = $request->password;

        return $this->ccbsService->ccbsLogin($username, $password);
    }

    public function saveCookie(Request $request) {
        $cookies = $request->input('cookies');
        file_put_contents(storage_path("app\CookiesCcos.txt"), $cookies);
        return 1;
    }

    public function doiSim(Request $request) {
        $sdt = $request->input('sdt');
        $esim = $request->input('esim');
        $ghichu = urlencode($request->input('ghichu'));

        return $this->ccbsService->doiSim($sdt, $esim, $ghichu);
    }

    function layMaSim(Request $request) {
        $sdt = $request->input('sdt');

        return $this->ccbsService->layMaSim($sdt);
    }

    function taiAnh(Request $request) {
        $ma = $request->input('ma');
        $bar = $request->input('bar');
        $sdt = $request->input('sdt');

        return $this->ccbsService->taiAnh($ma, $bar, $sdt);
    }

    public function checkMSIN(Request $request) {
        $msin = $request->input('msin');

        return $this->ccbsService->checkMSIN($msin);
    }

    public function layTTTBaoV4(Request $request) {
        $sdt = $request->input('sdt');
        $string_data = $request->input('string_data');

        return $this->ccbsService->layTTThueBaoV4($sdt, $string_data);
    }

    public function layTTTBao(Request $request) {
        $sdt = $request->input('sdt');
        $matinh = $request->input('matinh');

        return $this->ccbsService->layTTTBao($sdt, $matinh);
    }

    public function traCuuMI(Request $request) {
        $sdt = $request->input('sdt');

        return $this->ccosService->traCuuMI($sdt);
    }

    public function daoSim(Request $request) {
        $sdt = $request->input('sdt');
        $old_esim = $request->input('old_esim');
        $new_esim = $request->input('new_esim');
        $ghichu = urlencode($request->input('ghichu'));

        return $this->ccbsService->daoSim($sdt, $old_esim, $new_esim, $ghichu);
    }

    public function layDVu(Request $request) {
        $sdt = $request->input('sdt');
        $dich_vu = $request->input('dich_vu');

        return $this->ccbsService->layDVu($sdt, $dich_vu);
    }

    public function dmDVu(Request $request) {
        $sdt = $request->input('sdt');
        $dvu = $request->input('dvu');

        return $this->ccbsService->dmDVu($sdt, $dvu);
    }

    public function layIOC(Request $request) {
        $sdt = $request->input('sdt');

        return $this->ccbsService->layIOC($sdt);
    }

    public function catmoIOC(Request $request) {
        $sdt = $request->input('sdt');
        $goidi = $request->input('goidi');
        $goiden = $request->input('goiden');

        return $this->ccbsService->catmoIOC($sdt, $goidi, $goiden);
    }

    public function layBcEsim(Request $request) {
        $date = $request->input('date');

        $lay_bc_esim = $this->ccbsService->layBcEsim($date);
        if (is_string($lay_bc_esim)) return $lay_bc_esim;

        EsimReport::whereDate('date_time', Carbon::createFromFormat('d/m/Y', $date))->delete();

        foreach ($lay_bc_esim as $key => $value) {
            EsimReport::create([
                'date_time' => Carbon::createFromFormat('d/m/Y H:i:s', $value[1]),
                'mobile_number' => $value[2],
                'service_code' => $value[3],
                'action' => $value[4],
                'sub_type' => $value[5],
                'old_esim' => $value[6],
                'new_esim' => $value[7],
                'account' => $value[8],
                'note' => $value[9],
            ]);
        }

        return "Import thành công";
    }

    public function test() {
        return $this->ccbsService->test();
    }
}
