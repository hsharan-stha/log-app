<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureKioskDevice;
use App\Models\KioskDevice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function home(Request $request): View
    {
        $existing = KioskDevice::findActiveByPlainToken($request->cookie(EnsureKioskDevice::COOKIE));

        return view('attendance.home', [
            'existingDevice' => $existing,
        ]);
    }
}
