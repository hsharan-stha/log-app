<?php

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaceDescriptorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return view('office.dashboard', [
            'user' => $user,
            'roleLabel' => match ($user->role) {
                'staff' => 'Staff',
                'other' => 'Other staff',
                'hr' => 'HR',
                default => ucfirst((string) $user->role),
            },
        ]);
    }

    public function registerFace(): View
    {
        abort_unless(in_array(auth()->user()?->role, \App\Http\Controllers\Admin\OfficeUserController::ROLES, true), 404);

        return view('admin.staff.register-face', [
            'staff' => auth()->user(),
            'faceRegistrationContext' => 'self',
            'layout' => 'layouts.portal',
            'facePostUrl' => route('office.face.store'),
            'faceRedirectUrl' => route('office.dashboard'),
        ]);
    }

    public function storeFace(StoreFaceDescriptorRequest $request): RedirectResponse|JsonResponse
    {
        abort_unless(in_array($request->user()?->role, \App\Http\Controllers\Admin\OfficeUserController::ROLES, true), 404);

        $request->user()->update([
            'face_descriptor' => $request->validated('descriptor'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Face registered.',
                'redirect' => route('office.dashboard'),
            ]);
        }

        return redirect()->route('office.dashboard')->with('success', 'Face registered.');
    }
}
