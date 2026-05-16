<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FaceAttendanceController extends Controller
{
    private const MAX_SNAPSHOT_BYTES = 2_500_000;

    public function show(): View
    {
        return view('attendance.scan');
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'descriptor' => ['required', 'array', 'min:1'],
            'descriptor.*' => ['numeric'],
            'snapshot' => ['nullable', 'string', 'max:6500000'],
        ]);

        ['user' => $user] = face_find_matching_staff($data['descriptor'], 0.5);

        if (! $user instanceof User) {
            return response()->json([
                'ok' => false,
                'message' => 'No matching staff face found. Try again or ask an admin to register your face.',
            ], 422);
        }

        $tz = (string) config('app.timezone', 'UTC');
        /** Calendar day in APP_TIMEZONE — must match your office (set in .env). */
        $dateString = Carbon::now($tz)->toDateString();
        $clock = Carbon::now($tz);

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', $dateString)
            ->first();

        if ($attendance === null) {
            $attendance = new Attendance([
                'user_id' => $user->id,
                'attendance_date' => $dateString,
            ]);
        }

        if ($attendance->checkin_time === null) {
            $photoPath = $this->storeAttendanceSnapshot($data['snapshot'] ?? null, $user->id);
            $attendance->checkin_time = $clock;
            if ($photoPath !== null) {
                $attendance->checkin_photo_path = $photoPath;
            }
            $attendance->save();

            return response()->json([
                'ok' => true,
                'message' => 'Check-in recorded successfully.',
                'staff_name' => $user->name,
                'action' => 'checkin',
            ]);
        }

        if ($attendance->checkout_time === null) {
            $photoPath = $this->storeAttendanceSnapshot($data['snapshot'] ?? null, $user->id);
            $attendance->checkout_time = $clock;
            if ($photoPath !== null) {
                $attendance->checkout_photo_path = $photoPath;
            }
            $attendance->save();

            return response()->json([
                'ok' => true,
                'message' => 'Check-out recorded successfully.',
                'staff_name' => $user->name,
                'action' => 'checkout',
            ]);
        }

        return response()->json([
            'ok' => false,
            'message' => sprintf(
                'Attendance already completed for %s (calendar day in %s).',
                $dateString,
                $tz
            ),
            'staff_name' => $user->name,
            'attendance_date' => $dateString,
            'app_timezone' => $tz,
            'server_time' => $clock->toIso8601String(),
        ], 422);
    }

    /**
     * Accept a data URL (JPEG) from the kiosk canvas and store it on the public disk.
     */
    private function storeAttendanceSnapshot(?string $dataUrl, int $userId): ?string
    {
        if ($dataUrl === null || $dataUrl === '') {
            return null;
        }

        if (! str_starts_with($dataUrl, 'data:image/jpeg;base64,')) {
            return null;
        }

        $base64 = substr($dataUrl, strlen('data:image/jpeg;base64,'));
        $binary = base64_decode($base64, true);
        if ($binary === false || strlen($binary) < 100 || strlen($binary) > self::MAX_SNAPSHOT_BYTES) {
            return null;
        }

        $dir = 'attendance/'.Carbon::now((string) config('app.timezone', 'UTC'))->format('Y/m').'/'.$userId;
        $path = $dir.'/'.Str::uuid()->toString().'.jpg';

        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
