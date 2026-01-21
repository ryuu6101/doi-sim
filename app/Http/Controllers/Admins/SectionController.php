<?php

namespace App\Http\Controllers\Admins;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class SectionController extends Controller
{
    public function __construct() {
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
        View::share('delay', 2);
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

    public function toggleServices() {
        return view('admins.sections.toggle-services.index');
    }
}
