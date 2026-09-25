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
        $active = KioskDevice::active();

        return view('attendance.setup', [
            'existingDevice' => $existing,
            'blockingDevice' => $existing === null ? $active : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $existing = KioskDevice::findActiveByPlainToken($request->cookie(EnsureKioskDevice::COOKIE));
        if ($existing !== null) {
            return redirect()
                ->route('attendance.scan')
                ->with('success', 'This device is already registered as “'.$existing->name.'”.');
        }

        $active = KioskDevice::active();
        if ($active !== null) {
            return back()->withErrors([
                'name' => 'A kiosk is already registered (“'.$active->name.'”). Ask an admin to revoke it before registering this device.',
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'location' => ['required', 'in:school,bus'],
        ]);

        ['device' => $device, 'plain_token' => $plain] = KioskDevice::register(
            $data['name'],
            $data['location'],
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

        $label = $device->locationLabel();

        return redirect()
            ->route('attendance.scan')
            ->with('success', $label.' kiosk “'.$device->name.'” registered. This tablet can now open face attendance.')
            ->with('kiosk_plain_token', $plain)
            ->cookie($cookie);
    }
}
