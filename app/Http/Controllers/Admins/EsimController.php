<?php

namespace App\Http\Controllers\Admins;

use Exception;
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

        return $this->ccbsService->checkMSIN($msin);
    }

    public function layIMEI(Request $request) {
        $sdt = $request->input('sdt');

        return $this->ccbsService->layIMEI($sdt);
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

    public function test() {
        return $this->ccbsService->test();
    }
}
