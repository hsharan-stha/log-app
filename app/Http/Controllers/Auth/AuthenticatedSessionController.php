<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureKioskDevice;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\KioskDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $request->session()->forget('url.intended');

        return redirect()->route($user->homeRoute());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $registeredDevice = KioskDevice::findActiveByPlainToken($request->cookie(EnsureKioskDevice::COOKIE));

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($registeredDevice !== null) {
            return redirect()->route('attendance.scan');
        }

        return redirect()->route('login');
    }
}
