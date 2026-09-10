<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\View\View;

class AcademicSetupController extends Controller
{
    public function index(): View
    {
        $classCount = SchoolClass::query()->count();
        $teacherCount = User::query()->where('role', 'teacher')->count();
        $subjectCount = Subject::query()->count();
        $courseCount = Course::query()->where('is_active', true)->count();

        $recentCourses = Course::query()
            ->with(['schoolClass', 'subject', 'teacher'])
            ->latest('id')
            ->limit(8)
            ->get();

        $steps = [
            [
                'key' => 'classes',
                'step' => 1,
                'title' => 'Classes',
                'blurb' => 'Nursery to Grade 5',
                'count' => $classCount,
                'ready' => $classCount > 0,
                'index' => route('admin.classes.index'),
                'create' => route('admin.classes.create'),
                'create_label' => 'Add class',
            ],
            [
                'key' => 'teachers',
                'step' => 2,
                'title' => 'Teachers',
                'blurb' => 'People who teach courses',
                'count' => $teacherCount,
                'ready' => $teacherCount > 0,
                'index' => route('admin.staff.index'),
                'create' => route('admin.staff.create'),
                'create_label' => 'Add teacher',
            ],
            [
                'key' => 'subjects',
                'step' => 3,
                'title' => 'Subjects',
                'blurb' => 'English, Math, Japanese…',
                'count' => $subjectCount,
                'ready' => $subjectCount > 0,
                'index' => route('admin.subjects.index'),
                'create' => route('admin.subjects.create'),
                'create_label' => 'Add subject',
            ],
            [
                'key' => 'courses',
                'step' => 4,
                'title' => 'Courses',
                'blurb' => 'Class + subject + teacher',
                'count' => $courseCount,
                'ready' => $courseCount > 0,
                'index' => route('admin.courses.index'),
                'create' => route('admin.courses.create'),
                'create_label' => 'Assign course',
                'locked' => ! ($classCount && $teacherCount && $subjectCount),
            ],
        ];

        $canAssignCourse = $classCount > 0 && $teacherCount > 0 && $subjectCount > 0;

        return view('admin.academics.index', compact(
            'steps',
            'recentCourses',
            'canAssignCourse',
            'classCount',
            'teacherCount',
            'subjectCount',
            'courseCount'
        ));
    }
}
