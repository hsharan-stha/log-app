<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $classId = $request->string('class_id')->toString();
        $q = trim($request->string('q')->toString());

        $classes = SchoolClass::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $students = User::query()
            ->where('role', 'student')
            ->with('schoolClass')
            ->when($classId === 'unassigned', fn ($query) => $query->whereNull('class_id'))
            ->when(
                $classId !== '' && $classId !== 'all' && $classId !== 'unassigned',
                fn ($query) => $query->where('class_id', $classId)
            )
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('roll_number', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'classes' => $classes,
            'classId' => $classId !== '' ? $classId : 'all',
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedStudent($request);

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'class_id' => $data['class_id'],
            'roll_number' => $data['roll_number'],
            'password' => Hash::make(config('staff.default_password')),
            'role' => 'student',
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Student created.');
    }

    public function edit(User $student): View
    {
        abort_unless($student->isStudent(), 404);

        return view('admin.students.edit', [
            'student' => $student->load('guardians'),
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
            'guardians' => User::query()->where('role', 'guardian')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);

        $data = $this->validatedStudent($request, $student);
        $data += $request->validate([
            'password' => ['nullable', 'string', 'min:8'],
            'guardian_ids' => ['nullable', 'array'],
            'guardian_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $student->fill([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'class_id' => $data['class_id'],
            'roll_number' => $data['roll_number'],
        ]);

        if (! empty($data['password'])) {
            $student->password = Hash::make($data['password']);
        }

        $student->save();

        $guardianIds = $data['guardian_ids'] ?? [];
        $sync = [];
        foreach ($guardianIds as $gid) {
            $guardian = User::query()->find($gid);
            if ($guardian?->isGuardian()) {
                $sync[$gid] = ['relationship' => 'guardian'];
            }
        }
        $student->guardians()->sync($sync);

        return redirect()->route('admin.students.index')->with('success', 'Student updated.');
    }

    public function destroy(User $student): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted.');
    }

    public function registerFace(User $student): View
    {
        abort_unless($student->isStudent(), 404);

        return view('admin.students.register-face', ['student' => $student]);
    }

    public function storeFaceDescriptor(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);

        $data = $request->validate([
            'descriptor' => ['required', 'array', 'min:1'],
            'descriptor.*' => ['numeric'],
        ]);

        $student->update(['face_descriptor' => $data['descriptor']]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Face registered for '.$student->name.'.',
                'redirect' => route('admin.students.index'),
            ]);
        }

        return redirect()->route('admin.students.index')->with('success', 'Face registered.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedStudent(Request $request, ?User $student = null): array
    {
        $classId = $request->input('class_id');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student?->id)],
            'class_id' => ['required', 'exists:school_classes,id'],
            'roll_number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('users', 'roll_number')
                    ->where(fn ($query) => $query->where('class_id', $classId)->where('role', 'student'))
                    ->ignore($student?->id),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $data['roll_number'] = trim($data['roll_number']);

        return $data;
    }
}
