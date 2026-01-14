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

        View::share('username', $info[0] ?? '');
        View::share('password', $info[1] ?? '');
        View::share('cookies', $info[2] ?? '');
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
}
