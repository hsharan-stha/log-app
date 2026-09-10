<?php

namespace App\Http\Middleware;

use App\Models\KioskDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskDevice
{
    public const COOKIE = 'kiosk_device_token';

    public const HEADER = 'X-Kiosk-Device-Token';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->cookie(self::COOKIE)
            ?: $request->header(self::HEADER)
            ?: $request->input('device_token');

        $device = KioskDevice::findActiveByPlainToken(is_string($plain) ? $plain : null);

        if (! $device) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'This device is not authorized for face attendance. Sign in as the attendance operator and set up this kiosk.',
                ], 403);
            }

            return redirect()->route('attendance.unauthorized');
        }

        $device->touchLastSeen();
        $request->attributes->set('kiosk_device', $device);

        return $next($request);
    }
}
