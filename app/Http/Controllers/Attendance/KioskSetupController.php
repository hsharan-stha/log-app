<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureKioskDevice;
use App\Models\KioskDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KioskSetupController extends Controller
{
    public function show(Request $request): View
    {
        $existing = KioskDevice::findActiveByPlainToken($request->cookie(EnsureKioskDevice::COOKIE));

        return view('attendance.setup', [
            'existingDevice' => $existing,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        ['device' => $device, 'plain_token' => $plain] = KioskDevice::register(
            $data['name'],
            $request->user()
        );

        $cookie = cookie(
            EnsureKioskDevice::COOKIE,
            $plain,
            60 * 24 * 365 * 5,
            '/',
            null,
            $request->isSecure(),
            false,
            false,
            'Lax'
        );

        return redirect()
            ->route('attendance.scan')
            ->with('success', 'Device “'.$device->name.'” registered. This tablet can now open face attendance.')
            ->with('kiosk_plain_token', $plain)
            ->cookie($cookie);
    }
}
