<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Repositories\EsimHistories\EsimHistoryRepositoryInterface;
use App\Repositories\EsimReports\EsimReportRepositoryInterface;
use App\Services\BrandNameService;
use App\Services\CcbsServiceWrapper;
use App\Services\CcosService;
use App\Services\EsimService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EsimController extends Controller
{
    public function __construct(
        protected CcbsServiceWrapper $ccbsService,
        protected CcosService $ccosService,
        protected BrandNameService $brandNameService,
        protected EsimService $esimService,
        protected EsimReportRepositoryInterface $esimReportRepos,
        protected EsimHistoryRepositoryInterface $esimHistoryRepos,
    ) {}

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

        // return $this->ccbsService->doiSim($sdt, $esim, $ghichu);
        $doi_sim = $this->esimService->doiSim($sdt, $esim, $ghichu);

        if ($doi_sim['success']) $this->esimHistoryRepos->create(['sdt' => '84'.$sdt, 'esim' => $esim, 'ghichu' => $ghichu]);

        return $doi_sim;
    }

    public function kiemTraTrungTB(Request $request) {
        $sdt = $request->input('sdt');

        $this->esimHistoryRepos->deleteOld(today());

        return !!$this->esimHistoryRepos->getByMobileNumber($sdt);
    }

    function layMaSim(Request $request) {
        $sdt = $request->input('sdt');

        return $this->ccbsService->layMaSim($sdt);
    }

    function taiAnh(Request $request) {
        $ma = $request->input('ma');
        $bar = $request->input('bar');
        $sdt = $request->input('sdt');

        $result = $this->ccbsService->taiAnh($ma, $bar, $sdt);

        if (!$result) return 'Lỗi ngoại biên!';

        $file_path = "storage/qr_pdf/".$sdt.".pdf";
        file_put_contents($file_path, $result);
        
        return file_exists($file_path) ? asset($file_path) : false;
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

        if (count($lay_bc_esim) <= 0) return "Không tìm thấy kết quả";

        $today_reports = $this->esimReportRepos->filter(['date_time' => $date]);
        if($today_reports->count() > 0) $today_reports->toQuery()->delete();

        foreach ($lay_bc_esim as $key => $value) {
            $this->esimReportRepos->create([
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

        return "Đã import ".count($lay_bc_esim)." kết quả";
    }

    public function sendWelcomeMessage(Request $request) {
        $sdt = $request->input('sdt');
        $hotline = $request->input('hotline') ?? '';

        return $this->esimService->sendWelcomeMessage(sdt: $sdt, hotline: $hotline)['success'] ? 'THÀNH CÔNG' : 'THẤT BẠI';
    }

    public function kichHoatGPRS(Request $request) {
        $sdt = $request->input('sdt');

        return $this->esimService->kichHoatGPRS($sdt)['success'] ? 'THÀNH CÔNG' : 'THẤT BẠI';
    }

    public function kiemTraTTTBao(Request $request) {
        $sdt = $request->input('sdt');
        $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;

        $tttb_string_data = ['so_tb', 'esim', 'so_msin', 'goi_di', 'goi_den', 'loai_tb', 'ma_tinh', 'ngay_kh', 'pin', 'puk', 'pin2', 'puk2'];
        $ttkh_string_data = ['ngay_sinh', 'phai', 'so_gt', 'ngaycap_gt', 'doituong', 'ten_tb', 'ten_kh', 'diachi_chungtu', 
                                'diachi_thuongtru', 'diachi'];

        $lay_tttb = $this->ccbsService->layTTThueBaoV4($sdt, $tttb_string_data);

        if (str_contains($lay_tttb, 'OK|')) {
            $tttb_splited = explode('|', $lay_tttb);
            unset($tttb_splited[0]);
            $tttb = array_combine($tttb_string_data, $tttb_splited);

            $lay_ttkh = $this->ccbsService->layTTKhTb($sdt, $tttb['ma_tinh'], $ttkh_string_data);
            if (!str_contains($lay_ttkh, 'OK|')) $ttkh = [];
    
            $ttkh_splited = explode('|', $lay_ttkh);
            unset($ttkh_splited[0]);
            $ttkh = array_combine($ttkh_string_data, $ttkh_splited);
        } else {
            $tttb = [];
            $ttkh = [];
        }

        $dvu_tb = $this->ccbsService->docDvuTb($sdt);
        if (is_string($dvu_tb)) return;

        $ls_tb = $this->ccbsService->layLsTBao($sdt);
        if (is_string($ls_tb)) return;

        $ls_3g = $this->ccbsService->layLs3g($sdt);
        if (is_string($ls_3g)) return;

        return view('admins.sections.subscriber-check.mobile-info', compact('tttb', 'ttkh', 'dvu_tb', 'ls_tb', 'ls_3g'));
    }

    public function layLSuTBao(Request $request) {
        $sdt = $request->input('sdt');
        $sdt = strlen($sdt) < 11 ? '84'.$sdt : $sdt;

        $ls_tb = $this->ccbsService->layLsTBao($sdt);
        return is_array($ls_tb) ? $ls_tb : [];
    }
}
