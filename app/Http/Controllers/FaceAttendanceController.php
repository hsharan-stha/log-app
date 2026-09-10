<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Notifications\StudentAttendanceAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
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

    public function unauthorized(): View
    {
        return view('attendance.unauthorized');
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'descriptor' => ['required', 'array', 'min:1'],
            'descriptor.*' => ['numeric'],
            'snapshot' => ['nullable', 'string', 'max:6500000'],
        ]);

        ['user' => $user] = face_find_matching_person($data['descriptor'], 0.5);

        if (! $user instanceof User || ! $user->canUseFaceAttendance()) {
            return response()->json([
                'ok' => false,
                'message' => 'No matching student or teacher face found. Try again or ask the office to register the face.',
            ], 422);
        }

        $today = now()->toDateString();

        $attendance = Attendance::query()->firstOrNew([
            'user_id' => $user->id,
            'attendance_date' => $today,
        ]);

        if ($attendance->checkin_time === null) {
            $photoPath = $this->storeAttendanceSnapshot($data['snapshot'] ?? null, $user->id);
            $attendance->checkin_time = now();
            if ($photoPath !== null) {
                $attendance->checkin_photo_path = $photoPath;
            }
            $attendance->save();

            $this->notifyGuardiansIfStudent($user, 'checkin');

            return response()->json([
                'ok' => true,
                'message' => 'Check-in recorded successfully.',
                'staff_name' => $user->name,
                'person_name' => $user->name,
                'role' => $user->role,
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

            $this->notifyGuardiansIfStudent($user, 'checkout');

            return response()->json([
                'ok' => true,
                'message' => 'Check-out recorded successfully.',
                'staff_name' => $user->name,
                'person_name' => $user->name,
                'role' => $user->role,
                'action' => 'checkout',
            ]);
        }

        return response()->json([
            'ok' => false,
            'message' => 'Attendance already completed for today.',
            'staff_name' => $user->name,
            'person_name' => $user->name,
            'role' => $user->role,
        ], 422);
    }

    private function notifyGuardiansIfStudent(User $user, string $action): void
    {
        if (! $user->isStudent()) {
            return;
        }

        $user->loadMissing(['guardians', 'schoolClass']);

        if ($user->guardians->isEmpty()) {
            return;
        }

        Notification::send(
            $user->guardians,
            new StudentAttendanceAlert($user, $action, now()->format('H:i'))
        );
    }

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

        $dir = 'attendance/'.now()->format('Y/m').'/'.$userId;
        $path = $dir.'/'.Str::uuid()->toString().'.jpg';

        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
