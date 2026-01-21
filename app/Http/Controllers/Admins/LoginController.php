<?php

namespace App\Http\Controllers\Admins;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function getLogin() {
        return view('auth.sections.login.index');
    }

    public function authenticate(Request $request) {
        $credentials = $request->only('username', 'password');
        $credentials['is_actived'] = true;
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->route('home.index');
        }

        return redirect()->route('login')->with('error', 'Sai tên đăng nhập hoặc mật khẩu');
    }

    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
