<?php

namespace App\Http\Controllers\Admins;

use App\Actions\Google\HandleGoogleCallbackAction;
use App\Http\Controllers\Controller;
use App\Services\GoogleService;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function __construct(
        protected GoogleService $googleService
    ) {}

    public function redirect()
    {
        $url = $this->googleService
            ->getClient()
            ->createAuthUrl();

        return redirect()->away($url);
    }

    public function callback(Request $request, HandleGoogleCallbackAction $action)
    {
        if ($request->get('error')) {
            return redirect()
                ->route('home.index')
                ->with('error', 'Google authorization failed!');
        }

        $code = $request->get('code');

        if (!$code) {
            return redirect()
                ->route('home.index')
                ->with('error', 'Authorization code missing!');
        }

        $action->execute($code);

        return redirect()
            ->route('home.index')
            ->with('success', 'Google connected successfully!');
    }
}