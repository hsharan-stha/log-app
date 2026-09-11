<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\LessonPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CourseLessonTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_course_and_teacher_can_publish_lesson(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $class = SchoolClass::query()->create(['name' => 'UKG', 'code' => 'ukg', 'sort_order' => 1]);
        $subject = Subject::query()->create(['name' => 'English', 'code' => 'english', 'sort_order' => 1]);
        $student = User::factory()->create([
            'role' => 'student',
            'class_id' => $class->id,
            'email' => 'kid@example.com',
        ]);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'class_id' => $class->id,
            'subject_ids' => [$subject->id],
            'teacher_id' => $teacher->id,
            'academic_year' => '2026/2027',
        ])->assertRedirect(route('admin.courses.index'));

        $course = Course::query()->first();
        $this->assertNotNull($course);

        $this->actingAs($teacher)->post(route('teacher.lessons.store', $course), [
            'title' => 'Letter A',
            'body' => 'Learn letter A',
            'test_content' => 'Trace A',
            'lesson_date' => now()->toDateString(),
            'publish' => '1',
            'notify_students' => '1',
        ])->assertRedirect(route('teacher.lessons.show', [$course, Lesson::query()->first()]));

        $lesson = Lesson::query()->first();
        $this->assertTrue($lesson->isPublished());

        Notification::assertSentTo($student, LessonPublishedNotification::class);

        $this->actingAs($student)
            ->get(route('student.courses.index'))
            ->assertOk()
            ->assertSee('English');

        $this->actingAs($student)
            ->get(route('student.lessons.show', [$course, $lesson]))
            ->assertOk()
            ->assertSee('Letter A');
    }

    public function test_admin_can_filter_courses_by_class_subject_teacher_and_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacherA = User::factory()->create(['role' => 'teacher', 'name' => 'Teacher A']);
        $teacherB = User::factory()->create(['role' => 'teacher', 'name' => 'Teacher B']);
        $ukg = SchoolClass::query()->create(['name' => 'UKG', 'code' => 'ukg', 'sort_order' => 1]);
        $g1 = SchoolClass::query()->create(['name' => 'Grade 1', 'code' => 'g1', 'sort_order' => 2]);
        $english = Subject::query()->create(['name' => 'English', 'code' => 'english', 'sort_order' => 1]);
        $math = Subject::query()->create(['name' => 'Math', 'code' => 'math', 'sort_order' => 2]);

        $match = Course::query()->create([
            'class_id' => $ukg->id,
            'subject_id' => $english->id,
            'teacher_id' => $teacherA->id,
            'academic_year' => '2026/2027',
            'is_active' => true,
        ]);
        Course::query()->create([
            'class_id' => $g1->id,
            'subject_id' => $english->id,
            'teacher_id' => $teacherA->id,
            'academic_year' => '2026/2027',
            'is_active' => true,
        ]);
        Course::query()->create([
            'class_id' => $ukg->id,
            'subject_id' => $math->id,
            'teacher_id' => $teacherA->id,
            'academic_year' => '2026/2027',
            'is_active' => true,
        ]);
        Course::query()->create([
            'class_id' => $ukg->id,
            'subject_id' => $english->id,
            'teacher_id' => $teacherB->id,
            'academic_year' => '2025/2026',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.courses.index', [
                'class_id' => $ukg->id,
                'subject_id' => $english->id,
                'teacher_id' => $teacherA->id,
                'academic_year' => '2026/2027',
            ]))
            ->assertOk();

        $response->assertSee('UKG', false);
        $response->assertSee('English', false);
        $response->assertSee('Teacher A', false);
        $response->assertSee('2026/2027', false);

        // Table should contain only the matching course row (filters stay in the form dropdowns).
        $this->assertSame(1, substr_count($response->getContent(), 'btn-outline-secondary">Edit</a>'));
    }

    public function test_admin_can_assign_multiple_subjects_to_a_class(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $class = SchoolClass::query()->create(['name' => 'UKG', 'code' => 'ukg', 'sort_order' => 1]);
        $english = Subject::query()->create(['name' => 'English', 'code' => 'english', 'sort_order' => 1]);
        $math = Subject::query()->create(['name' => 'Math', 'code' => 'math', 'sort_order' => 2]);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'class_id' => $class->id,
            'subject_ids' => [$english->id, $math->id],
            'teacher_id' => $teacher->id,
            'academic_year' => '2026/2027',
        ])
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success', '2 courses assigned.');

        $this->assertDatabaseCount('courses', 2);
        $this->assertDatabaseHas('courses', [
            'class_id' => $class->id,
            'subject_id' => $english->id,
            'teacher_id' => $teacher->id,
        ]);
        $this->assertDatabaseHas('courses', [
            'class_id' => $class->id,
            'subject_id' => $math->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_teacher_cannot_edit_another_teachers_course(): void
    {
        $teacherA = User::factory()->create(['role' => 'teacher']);
        $teacherB = User::factory()->create(['role' => 'teacher']);
        $class = SchoolClass::query()->create(['name' => 'G1', 'code' => 'g1', 'sort_order' => 1]);
        $subject = Subject::query()->create(['name' => 'Math', 'code' => 'math', 'sort_order' => 1]);
        $course = Course::query()->create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacherA->id,
            'is_active' => true,
        ]);

        $this->actingAs($teacherB)
            ->get(route('teacher.courses.show', $course))
            ->assertForbidden();
    }

    public function test_teacher_can_update_lesson_that_already_has_whiteboards(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $class = SchoolClass::query()->create(['name' => 'UKG', 'code' => 'ukg', 'sort_order' => 1]);
        $subject = Subject::query()->create(['name' => 'English', 'code' => 'english', 'sort_order' => 1]);
        $course = Course::query()->create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'is_active' => true,
        ]);
        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Old title',
            'body' => 'Old body',
            'lesson_date' => now()->toDateString(),
            'published_at' => now(),
        ]);
        $lesson->whiteboards()->create([
            'title' => 'Board 1',
            'path' => 'lessons/'.$lesson->id.'/whiteboards/demo.png',
            'sort_order' => 1,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.lessons.edit', [$course, $lesson]))
            ->assertOk()
            ->assertSee('id="lesson-edit-form"', false)
            ->assertSee('id="delete-whiteboard-'.$lesson->whiteboards->first()->id.'"', false);

        $this->actingAs($teacher)
            ->put(route('teacher.lessons.update', [$course, $lesson]), [
                'title' => 'Updated title',
                'body' => 'Updated body',
                'lesson_date' => now()->toDateString(),
                'publish' => '1',
            ])
            ->assertRedirect(route('teacher.lessons.show', [$course, $lesson]));

        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'title' => 'Updated title',
        ]);
        $this->assertDatabaseHas('lesson_whiteboards', [
            'lesson_id' => $lesson->id,
            'title' => 'Board 1',
        ]);
    }
}
