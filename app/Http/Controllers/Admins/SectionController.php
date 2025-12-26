<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function home() {
        $file = file_get_contents(storage_path('app\Login.txt'));
        $info = [];
        if ($file) $info = explode("\n", $file);

        $username = $info[0] ?? '';
        $password = $info[1] ?? '';
        $cookies = $info[2] ?? '';

        return view('admins.sections.home.index')->with([
            'username' => $username,
            'password' => $password,
            'cookies' => $cookies,
        ]);
    }
}
