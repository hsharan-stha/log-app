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
            'openSlots' => KioskDevice::registrationSlots(),
            'activeDevices' => KioskDevice::query()->whereNull('revoked_at')->orderBy('name')->get(),
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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slot' => ['required', 'in:teachers'],
        ]);

        $blocking = KioskDevice::deviceBlockingSlot($data['slot']);
        if ($blocking !== null) {
            return back()->withErrors([
                'slot' => '“'.$blocking->name.'” already uses this kiosk. Ask an admin to revoke it before registering another.',
            ])->withInput();
        }

        $slot = KioskDevice::SLOTS[$data['slot']];

        ['device' => $device, 'plain_token' => $plain] = KioskDevice::register(
            $data['name'],
            $slot['location'],
            $request->user(),
            $slot['audience']
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
            ->with('success', $device->audienceLabel().' · '.$device->locationLabel().' kiosk “'.$device->name.'” registered. This tablet can now open face attendance.')
            ->with('kiosk_plain_token', $plain)
            ->cookie($cookie);
    }
}
