<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->with(['schoolClass', 'subject', 'teacher'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('admin.courses.create', [
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
            'subjects' => Subject::query()->orderBy('sort_order')->get(),
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'academic_year' => ['nullable', 'string', 'max:20'],
        ]);

        $teacher = User::query()->findOrFail($data['teacher_id']);
        abort_unless($teacher->isTeacher(), 422);

        $year = $data['academic_year'] ?: null;

        $exists = Course::query()
            ->where('class_id', $data['class_id'])
            ->where('subject_id', $data['subject_id'])
            ->where(function ($q) use ($year) {
                if ($year === null) {
                    $q->whereNull('academic_year');
                } else {
                    $q->where('academic_year', $year);
                }
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'subject_id' => 'This subject is already assigned to that class for the selected year.',
            ]);
        }

        Course::query()->create([
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'teacher_id' => $data['teacher_id'],
            'academic_year' => $year,
            'is_active' => true,
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'Course assigned.');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', [
            'course' => $course,
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
            'subjects' => Subject::query()->orderBy('sort_order')->get(),
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $teacher = User::query()->findOrFail($data['teacher_id']);
        abort_unless($teacher->isTeacher(), 422);

        $year = $data['academic_year'] ?: null;

        $duplicate = Course::query()
            ->where('class_id', $data['class_id'])
            ->where('subject_id', $data['subject_id'])
            ->whereKeyNot($course->id)
            ->where(function ($q) use ($year) {
                if ($year === null) {
                    $q->whereNull('academic_year');
                } else {
                    $q->where('academic_year', $year);
                }
            })
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'subject_id' => 'This subject is already assigned to that class for the selected year.',
            ]);
        }

        $course->update([
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'teacher_id' => $data['teacher_id'],
            'academic_year' => $year,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted.');
    }
}
