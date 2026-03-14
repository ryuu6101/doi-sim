<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    public function login(Request $request) {
        $credentials = $request->only('username', 'password');
        $credentials['is_actived'] = true;

        if (Auth::attempt($credentials)) {
            PersonalAccessToken::where('created_at', '<', now()->subMinutes(config('sanctum.expiration')))->delete();
            $token = Auth::user()->createToken('esim-token')->plainTextToken;
            return response()->json([
                'message' => 'Đăng nhập thành công',
                'token' => $token,
            ]);
        }

        return response()->json(['message' => 'Sai thông tin đăng nhập!'], 401);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Đã đăng xuất']);
    } 
}
