<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\LessonWhiteboard;
use App\Models\User;
use App\Notifications\LessonPublishedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->with(['schoolClass', 'subject'])
            ->withCount('lessons')
            ->where('teacher_id', $request->user()->id)
            ->where('is_active', true)
            ->orderBy('class_id')
            ->paginate(20);

        return view('teacher.courses.index', compact('courses'));
    }

    public function show(Request $request, Course $course): View
    {
        $this->ensureOwnsCourse($request, $course);

        $lessons = $course->lessons()
            ->withCount('attachments')
            ->orderByDesc('lesson_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('teacher.courses.show', compact('course', 'lessons'));
    }

    public function createLesson(Request $request, Course $course): View
    {
        $this->ensureOwnsCourse($request, $course);

        return view('teacher.lessons.create', compact('course'));
    }

    public function storeLesson(Request $request, Course $course): RedirectResponse
    {
        $this->ensureOwnsCourse($request, $course);

        $data = $this->validatedLesson($request);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'test_content' => $data['test_content'] ?? null,
            'lesson_date' => $data['lesson_date'],
            'recording_url' => $data['recording_url'] ?? null,
            'published_at' => $request->boolean('publish') ? now() : null,
        ]);

        $this->storeAttachments($request, $lesson);
        $this->storeWhiteboards($request, $lesson);

        if ($lesson->isPublished() && $request->boolean('notify_students')) {
            $this->notifyClass($lesson);
        }

        return redirect()
            ->route('teacher.lessons.show', [$course, $lesson])
            ->with('success', 'Lesson saved.');
    }

    public function showLesson(Request $request, Course $course, Lesson $lesson): View
    {
        $this->ensureOwnsCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->load(['attachments', 'whiteboards']);
        $course->load(['subject', 'schoolClass']);

        return view('teacher.lessons.show', compact('course', 'lesson'));
    }

    public function editLesson(Request $request, Course $course, Lesson $lesson): View
    {
        $this->ensureOwnsCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->load(['attachments', 'whiteboards']);

        return view('teacher.lessons.edit', compact('course', 'lesson'));
    }

    public function updateLesson(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->ensureOwnsCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $data = $this->validatedLesson($request);
        $wasPublished = $lesson->isPublished();

        $lesson->update([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'test_content' => $data['test_content'] ?? null,
            'lesson_date' => $data['lesson_date'],
            'recording_url' => $data['recording_url'] ?? null,
            'published_at' => $request->boolean('publish')
                ? ($lesson->published_at ?? now())
                : null,
        ]);

        $this->storeAttachments($request, $lesson);
        $this->storeWhiteboards($request, $lesson);

        if ($request->boolean('publish') && $request->boolean('notify_students') && (! $wasPublished || ! $lesson->email_sent)) {
            $this->notifyClass($lesson->fresh());
        }

        return redirect()
            ->route('teacher.lessons.show', [$course, $lesson])
            ->with('success', 'Lesson updated.');
    }

    public function destroyLesson(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->ensureOwnsCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        foreach ($lesson->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->path);
            $attachment->delete();
        }

        foreach ($lesson->whiteboards as $board) {
            Storage::disk('public')->delete($board->path);
            $board->delete();
        }

        $lesson->delete();

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', 'Lesson deleted.');
    }

    public function destroyAttachment(Request $request, Course $course, Lesson $lesson, LessonAttachment $attachment): RedirectResponse
    {
        $this->ensureOwnsCourse($request, $course);
        abort_unless($lesson->course_id === $course->id && $attachment->lesson_id === $lesson->id, 404);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

    public function destroyWhiteboard(Request $request, Course $course, Lesson $lesson, LessonWhiteboard $whiteboard): RedirectResponse
    {
        $this->ensureOwnsCourse($request, $course);
        abort_unless($lesson->course_id === $course->id && $whiteboard->lesson_id === $lesson->id, 404);

        Storage::disk('public')->delete($whiteboard->path);
        $whiteboard->delete();

        return back()->with('success', 'Whiteboard removed.');
    }

    private function ensureOwnsCourse(Request $request, Course $course): void
    {
        abort_unless(
            $course->teacher_id === $request->user()->id && $course->is_active,
            403
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedLesson(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string'],
            'test_content' => ['nullable', 'string'],
            'lesson_date' => ['required', 'date'],
            'recording_url' => ['nullable', 'string', 'max:500', 'url'],
            'publish' => ['nullable', 'boolean'],
            'notify_students' => ['nullable', 'boolean'],
            'attachments' => ['sometimes', 'nullable', 'array', 'max:10'],
            'attachments.*' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx'],
            'whiteboards' => ['sometimes', 'nullable', 'array', 'max:8'],
            'whiteboards.*' => ['nullable', 'file', 'max:10240', 'mimes:png,jpg,jpeg,webp'],
            'whiteboard_titles' => ['sometimes', 'nullable', 'array', 'max:8'],
            'whiteboard_titles.*' => ['nullable', 'string', 'max:80'],
        ]);
    }

    private function storeAttachments(Request $request, Lesson $lesson): void
    {
        $files = collect($request->file('attachments', []))
            ->filter(fn ($file) => $file && $file->isValid());

        if ($files->isEmpty()) {
            return;
        }

        foreach ($files as $file) {
            $mime = $file->getMimeType() ?? '';
            $kind = str_starts_with($mime, 'image/') ? 'image'
                : ($mime === 'application/pdf' ? 'pdf' : 'file');

            $path = $file->store('lessons/'.$lesson->id, 'public');

            LessonAttachment::query()->create([
                'lesson_id' => $lesson->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $mime,
                'kind' => $kind,
                'size' => $file->getSize() ?: 0,
            ]);
        }
    }

    private function storeWhiteboards(Request $request, Lesson $lesson): void
    {
        $files = collect($request->file('whiteboards', []))
            ->filter(fn ($file) => $file && $file->isValid())
            ->values();

        if ($files->isEmpty()) {
            return;
        }

        $titles = $request->input('whiteboard_titles', []);
        $sort = (int) $lesson->whiteboards()->max('sort_order');

        foreach ($files as $index => $file) {
            $sort++;
            $path = $file->store('lessons/'.$lesson->id.'/whiteboards', 'public');
            $title = trim((string) ($titles[$index] ?? ''));
            if ($title === '') {
                $title = 'Board '.$sort;
            }

            LessonWhiteboard::query()->create([
                'lesson_id' => $lesson->id,
                'title' => $title,
                'path' => $path,
                'sort_order' => $sort,
            ]);
        }
    }

    private function notifyClass(Lesson $lesson): void
    {
        $lesson->loadMissing('course');
        $classId = $lesson->course?->class_id;
        if (! $classId) {
            return;
        }

        $students = User::query()
            ->where('role', 'student')
            ->where('class_id', $classId)
            ->whereNotNull('email')
            ->get();

        if ($students->isNotEmpty()) {
            Notification::send($students, new LessonPublishedNotification($lesson));
        }

        $lesson->forceFill(['email_sent' => true])->save();
    }
}
