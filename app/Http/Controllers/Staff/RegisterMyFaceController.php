<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaceDescriptorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RegisterMyFaceController extends Controller
{
    public function show(): View
    {
        $staff = auth()->user();

        return view('admin.staff.register-face', [
            'staff' => $staff,
            'layout' => 'layouts.staff',
            'faceRegistrationContext' => 'self',
        ]);
    }

    public function store(StoreFaceDescriptorRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'face_descriptor' => $request->validated('descriptor'),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Your face was saved. You can use the attendance kiosk now.',
            'redirect' => route('staff.register-face'),
        ]);
    }
}
