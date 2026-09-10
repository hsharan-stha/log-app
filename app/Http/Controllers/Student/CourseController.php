<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $courses = Course::query()
            ->with(['subject', 'teacher'])
            ->where('class_id', $user->class_id)
            ->where('is_active', true)
            ->orderBy('subject_id')
            ->paginate(20);

        return view('student.courses.index', [
            'user' => $user->load('schoolClass'),
            'courses' => $courses,
        ]);
    }

    public function show(Request $request, Course $course): View
    {
        $this->ensureStudentInCourse($request, $course);

        $lessons = $course->lessons()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->withCount('attachments')
            ->orderByDesc('lesson_date')
            ->orderByDesc('id')
            ->paginate(20);

        $course->load(['subject', 'teacher', 'schoolClass']);

        return view('student.courses.show', compact('course', 'lessons'));
    }

    public function showLesson(Request $request, Course $course, Lesson $lesson): View
    {
        $this->ensureStudentInCourse($request, $course);
        abort_unless($lesson->course_id === $course->id && $lesson->isPublished(), 404);

        $lesson->load(['attachments', 'whiteboards']);
        $course->load(['subject', 'teacher']);

        return view('student.lessons.show', compact('course', 'lesson'));
    }

    private function ensureStudentInCourse(Request $request, Course $course): void
    {
        $user = $request->user();
        abort_unless(
            $course->is_active && (int) $course->class_id === (int) $user->class_id,
            403
        );
    }
}
