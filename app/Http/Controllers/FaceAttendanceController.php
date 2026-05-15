<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
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

        /** Calendar date in app timezone (see config/app.php → APP_TIMEZONE) */
        $attendanceDate = Date::today()->toDateString();

        $attendance = Attendance::query()->firstOrNew([
            'user_id' => $user->id,
            'attendance_date' => $attendanceDate,
        ]);

        if ($attendance->checkin_time === null) {
            $photoPath = $this->storeAttendanceSnapshot($data['snapshot'] ?? null, $user->id);
            $attendance->checkin_time = now();
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
            $attendance->checkout_time = now();
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
            'message' => 'Attendance already completed for today.',
            'staff_name' => $user->name,
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

        $dir = 'attendance/'.Date::now()->format('Y/m').'/'.$userId;
        $path = $dir.'/'.Str::uuid()->toString().'.jpg';

        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
