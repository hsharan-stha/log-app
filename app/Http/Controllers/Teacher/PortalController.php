<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaceDescriptorRequest;
use App\Models\Attendance;
use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function attendance(Request $request): View
    {
        $user = $request->user();
        $records = Attendance::query()
            ->where('user_id', $user->id)
            ->orderByDesc('attendance_date')
            ->paginate(20);

        return view('teacher.attendance', compact('records'));
    }

    public function classStudents(Request $request): View
    {
        $classIds = SchoolClass::query()
            ->where('homeroom_teacher_id', $request->user()->id)
            ->pluck('id');

        $students = User::query()
            ->where('role', 'student')
            ->whereIn('class_id', $classIds)
            ->with('schoolClass')
            ->orderBy('name')
            ->paginate(20);

        return view('teacher.students', [
            'students' => $students,
            'hasHomeroom' => $classIds->isNotEmpty(),
        ]);
    }

    public function notices(Request $request): View
    {
        $user = $request->user();
        $classIds = SchoolClass::query()
            ->where('homeroom_teacher_id', $user->id)
            ->pluck('id');

        $notices = Notice::query()
            ->with('schoolClass')
            ->where(function ($q) use ($user, $classIds) {
                $q->where('author_id', $user->id)
                    ->orWhere(function ($visible) use ($classIds) {
                        $visible->whereNotNull('published_at')
                            ->where('published_at', '<=', now())
                            ->where(function ($audience) use ($classIds) {
                                $audience->whereIn('audience', ['all', 'teachers'])
                                    ->orWhere(function ($classAudience) use ($classIds) {
                                        $classAudience->where('audience', 'class')
                                            ->whereIn('class_id', $classIds);
                                    });
                            });
                    });
            })
            ->latest()
            ->paginate(20);

        $classes = SchoolClass::query()
            ->where('homeroom_teacher_id', $user->id)
            ->orderBy('sort_order')
            ->get();

        return view('teacher.notices', compact('notices', 'classes'));
    }

    public function storeNotice(Request $request): RedirectResponse
    {
        $user = $request->user();
        $classIds = SchoolClass::query()
            ->where('homeroom_teacher_id', $user->id)
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
            'class_id' => ['required', Rule::in($classIds)],
        ]);

        Notice::query()->create([
            'author_id' => $user->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => 'class',
            'class_id' => $data['class_id'],
            'published_at' => now(),
        ]);

        return back()->with('success', 'Notice posted to class.');
    }

    public function registerFace(): View
    {
        return view('teacher.register-face', [
            'staff' => auth()->user(),
            'faceRegistrationContext' => 'self',
            'layout' => 'layouts.portal',
        ]);
    }

    public function storeFace(StoreFaceDescriptorRequest $request): RedirectResponse|JsonResponse
    {
        $request->user()->update([
            'face_descriptor' => $request->validated('descriptor'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Face registered.',
                'redirect' => route('teacher.dashboard'),
            ]);
        }

        return redirect()->route('teacher.dashboard')->with('success', 'Face registered.');
    }
}
