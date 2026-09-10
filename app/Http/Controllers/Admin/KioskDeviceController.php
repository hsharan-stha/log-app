<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KioskDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KioskDeviceController extends Controller
{
    public function index(): View
    {
        $devices = KioskDevice::query()
            ->with('registrar')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.devices.index', compact('devices'));
    }

    public function revoke(KioskDevice $device): RedirectResponse
    {
        $device->revoke();

        return redirect()->route('admin.devices.index')->with('success', 'Device revoked. It can no longer open face attendance.');
    }
}
