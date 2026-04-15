<?php

namespace App\Http\Controllers\Admins;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Repositories\EsimReports\EsimReportRepositoryInterface;

class SectionController extends Controller
{
    public function __construct(protected EsimReportRepositoryInterface $esimReportRepos) {
        $info = [];
        if (file_exists(storage_path('app\Login.txt'))) {
            $file = file_get_contents(storage_path('app\Login.txt'));
            $info = explode("\n", $file);
        }

        $cookies_ccos = "";
        if (file_exists(storage_path('app\CookiesCcos.txt'))) {
            $cookies_ccos = file_get_contents(storage_path('app\CookiesCcos.txt'));
        }

        View::share('username', $info[0] ?? '');
        View::share('password', $info[1] ?? '');
        View::share('cookies', $info[2] ?? '');
        View::share('cookies_ccos', $cookies_ccos);
        View::share('delay', 5);
    }

    public function home() {
        return view('admins.sections.home.index');
    }

    public function msinCheck() {
        return view('admins.sections.msin-check.index');
    }

    public function mobileCheck() {
        return view('admins.sections.mobile-check.index');
    }

    public function miCheck() {
        return view('admins.sections.mi-check.index');
    }

    public function swapIMEI() {
        return view('admins.sections.swap-imei.index');
    }

    public function toggleGPRS() {
        return view('admins.sections.toggle-gprs.index');
    }

    public function toggleIOC() {
        return view('admins.sections.toggle-ioc.index');
    }

    public function toggleSmtSmo() {
        return view('admins.sections.toggle-smt-smo.index');
    }

    public function importEsimReport() {
        return view('admins.sections.esim-report.import.index');
    }

    public function listEsimReport(Request $request) {
        $params = $request->params ?? [];
        $paginate = $request->paginate ?? 50;

        $esim_reports = $this->esimReportRepos->filter($params, $paginate, 'desc', 'date_time');

        if ($request->ajax()) {
            return view('admins.sections.esim-report.statistical.list-partial', compact('esim_reports'));
        }

        $service_codes = $this->esimReportRepos->getColumn('service_code');
        $actions = $this->esimReportRepos->getColumn('action');
        $sub_types = $this->esimReportRepos->getColumn('sub_type');
        $accounts = $this->esimReportRepos->getColumn('account');

        return view('admins.sections.esim-report.statistical.index', 
                compact('esim_reports', 'service_codes', 'actions', 'sub_types', 'accounts'));
    }

    public function toggleServices() {
        return view('admins.sections.toggle-services.index');
    }

    public function subscriberCheck() {
        return view('admins.sections.subscriber-check.index');
    }
}
