<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\KioskDevice;
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

    public function show(Request $request): View
    {
        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');

        return view('attendance.scan', [
            'kioskLocation' => $device?->location ?? 'school',
            'kioskName' => $device?->name,
        ]);
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
                'message' => 'No matching face found. Try again or ask the office to register the face.',
            ], 422);
        }

        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');
        $location = $device?->location === 'bus' ? 'bus' : 'school';

        $today = now()->toDateString();

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first() ?? new Attendance([
                'user_id' => $user->id,
                'attendance_date' => $today,
            ]);

        if (! $user->isStudent()) {
            return $this->recordTeacherSchoolAttendance($attendance, $user, $data['snapshot'] ?? null);
        }

        if ($location === 'bus') {
            return $this->recordStudentBusAttendance($attendance, $user, $data['snapshot'] ?? null);
        }

        return $this->recordStudentSchoolAttendance($attendance, $user, $data['snapshot'] ?? null);
    }

    private function recordTeacherSchoolAttendance(Attendance $attendance, User $user, ?string $snapshot): JsonResponse
    {
        if ($attendance->checkin_time === null) {
            return $this->saveAndRespond(
                $attendance,
                $user,
                schoolCheckin: true,
                snapshot: $snapshot,
                action: 'school_checkin',
                message: 'Check-in recorded successfully.',
                notify: false,
            );
        }

        if ($attendance->checkout_time === null) {
            return $this->saveAndRespond(
                $attendance,
                $user,
                schoolCheckout: true,
                snapshot: $snapshot,
                action: 'school_checkout',
                message: 'Check-out recorded successfully.',
                notify: false,
            );
        }

        return $this->alreadyDone($user);
    }

    private function recordStudentBusAttendance(Attendance $attendance, User $user, ?string $snapshot): JsonResponse
    {
        if (! $user->rides_bus) {
            return response()->json([
                'ok' => false,
                'message' => 'This student is not marked for the school bus. Use the school kiosk, or ask the office to enable bus for this student.',
                'person_name' => $user->name,
                'role' => $user->role,
            ], 422);
        }

        if ($attendance->bus_checkin_time === null) {
            return $this->saveAndRespond(
                $attendance,
                $user,
                busCheckin: true,
                snapshot: $snapshot,
                action: 'bus_checkin',
                message: 'Bus check-in recorded successfully.',
                notify: true,
            );
        }

        if ($attendance->checkin_time === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Already checked in on the bus. Next: check in at the school kiosk.',
                'person_name' => $user->name,
                'role' => $user->role,
            ], 422);
        }

        if ($attendance->checkout_time === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Check out at the school kiosk first, then scan again on the bus.',
                'person_name' => $user->name,
                'role' => $user->role,
            ], 422);
        }

        if ($attendance->bus_checkout_time === null) {
            return $this->saveAndRespond(
                $attendance,
                $user,
                busCheckout: true,
                snapshot: $snapshot,
                action: 'bus_checkout',
                message: 'Bus check-out recorded successfully.',
                notify: true,
            );
        }

        return $this->alreadyDone($user);
    }

    private function recordStudentSchoolAttendance(Attendance $attendance, User $user, ?string $snapshot): JsonResponse
    {
        if ($user->rides_bus && $attendance->bus_checkin_time === null) {
            return response()->json([
                'ok' => false,
                'message' => 'This student rides the bus. Check in at the bus kiosk first.',
                'person_name' => $user->name,
                'role' => $user->role,
            ], 422);
        }

        if ($attendance->checkin_time === null) {
            return $this->saveAndRespond(
                $attendance,
                $user,
                schoolCheckin: true,
                snapshot: $snapshot,
                action: 'school_checkin',
                message: 'School check-in recorded successfully.',
                notify: true,
            );
        }

        if ($attendance->checkout_time === null) {
            return $this->saveAndRespond(
                $attendance,
                $user,
                schoolCheckout: true,
                snapshot: $snapshot,
                action: 'school_checkout',
                message: 'School check-out recorded successfully.',
                notify: true,
            );
        }

        if ($user->rides_bus && $attendance->bus_checkout_time === null) {
            return response()->json([
                'ok' => false,
                'message' => 'School attendance complete. Next: check out on the bus kiosk.',
                'person_name' => $user->name,
                'role' => $user->role,
            ], 422);
        }

        return $this->alreadyDone($user);
    }

    private function saveAndRespond(
        Attendance $attendance,
        User $user,
        ?string $snapshot,
        string $action,
        string $message,
        bool $notify,
        bool $busCheckin = false,
        bool $busCheckout = false,
        bool $schoolCheckin = false,
        bool $schoolCheckout = false,
    ): JsonResponse {
        $photoPath = $this->storeAttendanceSnapshot($snapshot, $user->id);

        if ($busCheckin) {
            $attendance->bus_checkin_time = now();
            if ($photoPath !== null) {
                $attendance->bus_checkin_photo_path = $photoPath;
            }
        } elseif ($busCheckout) {
            $attendance->bus_checkout_time = now();
            if ($photoPath !== null) {
                $attendance->bus_checkout_photo_path = $photoPath;
            }
        } elseif ($schoolCheckin) {
            $attendance->checkin_time = now();
            if ($photoPath !== null) {
                $attendance->checkin_photo_path = $photoPath;
            }
        } elseif ($schoolCheckout) {
            $attendance->checkout_time = now();
            if ($photoPath !== null) {
                $attendance->checkout_photo_path = $photoPath;
            }
        }

        $attendance->save();

        if ($notify) {
            $this->notifyGuardiansIfStudent($user, $action);
        }

        return response()->json([
            'ok' => true,
            'message' => $message,
            'staff_name' => $user->name,
            'person_name' => $user->name,
            'role' => $user->role,
            'action' => $action,
        ]);
    }

    private function alreadyDone(User $user): JsonResponse
    {
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
