<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::query()
            ->with('homeroomTeacher')
            ->withCount('students')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.classes.index', compact('classes'));
    }

    public function create(): View
    {
        return view('admin.classes.create', [
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:32', 'unique:school_classes,code'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        SchoolClass::query()->create([
            'name' => $data['name'],
            'code' => strtolower($data['code']),
            'sort_order' => $data['sort_order'] ?? 0,
            'homeroom_teacher_id' => $data['homeroom_teacher_id'] ?? null,
        ]);

        return redirect()->route('admin.classes.index')->with('success', 'Class created.');
    }

    public function edit(SchoolClass $class): View
    {
        return view('admin.classes.edit', [
            'class' => $class,
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:32', Rule::unique('school_classes', 'code')->ignore($class->id)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        $class->update([
            'name' => $data['name'],
            'code' => strtolower($data['code']),
            'sort_order' => $data['sort_order'] ?? 0,
            'homeroom_teacher_id' => $data['homeroom_teacher_id'] ?? null,
        ]);

        return redirect()->route('admin.classes.index')->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $class->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Class deleted.');
    }
}
