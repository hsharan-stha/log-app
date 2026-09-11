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
    public function index(Request $request): View
    {
        $classId = $request->string('class_id')->toString() ?: 'all';
        $subjectId = $request->string('subject_id')->toString() ?: 'all';
        $teacherId = $request->string('teacher_id')->toString() ?: 'all';
        $year = trim($request->string('academic_year')->toString());

        $courses = Course::query()
            ->with(['schoolClass', 'subject', 'teacher'])
            ->when(
                $classId !== 'all' && $classId !== '',
                fn ($query) => $query->where('class_id', $classId)
            )
            ->when(
                $subjectId !== 'all' && $subjectId !== '',
                fn ($query) => $query->where('subject_id', $subjectId)
            )
            ->when(
                $teacherId !== 'all' && $teacherId !== '',
                fn ($query) => $query->where('teacher_id', $teacherId)
            )
            ->when($year !== '', function ($query) use ($year) {
                $query->where('academic_year', $year);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $years = Course::query()
            ->whereNotNull('academic_year')
            ->where('academic_year', '!=', '')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('admin.courses.index', [
            'courses' => $courses,
            'classes' => SchoolClass::query()->orderBy('sort_order')->get(),
            'subjects' => Subject::query()->orderBy('sort_order')->get(),
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
            'years' => $years,
            'classId' => $classId,
            'subjectId' => $subjectId,
            'teacherId' => $teacherId,
            'year' => $year,
        ]);
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
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id', 'distinct'],
            'teacher_id' => ['required', 'exists:users,id'],
            'academic_year' => ['nullable', 'string', 'max:20'],
        ]);

        $teacher = User::query()->findOrFail($data['teacher_id']);
        abort_unless($teacher->isTeacher(), 422);

        $year = $data['academic_year'] ?: null;
        $subjectIds = array_values(array_unique(array_map('intval', $data['subject_ids'])));

        $alreadyAssigned = Course::query()
            ->where('class_id', $data['class_id'])
            ->whereIn('subject_id', $subjectIds)
            ->where(function ($q) use ($year) {
                if ($year === null) {
                    $q->whereNull('academic_year');
                } else {
                    $q->where('academic_year', $year);
                }
            })
            ->pluck('subject_id')
            ->all();

        if ($alreadyAssigned !== []) {
            $names = Subject::query()
                ->whereIn('id', $alreadyAssigned)
                ->orderBy('sort_order')
                ->pluck('name')
                ->implode(', ');

            return back()->withInput()->withErrors([
                'subject_ids' => 'Already assigned to that class for the selected year: '.$names.'.',
            ]);
        }

        foreach ($subjectIds as $subjectId) {
            Course::query()->create([
                'class_id' => $data['class_id'],
                'subject_id' => $subjectId,
                'teacher_id' => $data['teacher_id'],
                'academic_year' => $year,
                'is_active' => true,
            ]);
        }

        $count = count($subjectIds);
        $message = $count === 1
            ? 'Course assigned.'
            : $count.' courses assigned.';

        return redirect()->route('admin.courses.index')->with('success', $message);
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
