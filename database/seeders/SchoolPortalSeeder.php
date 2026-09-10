<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SchoolPortalSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Nursery', 'code' => 'nursery', 'sort_order' => 1],
            ['name' => 'LKG', 'code' => 'lkg', 'sort_order' => 2],
            ['name' => 'UKG', 'code' => 'ukg', 'sort_order' => 3],
            ['name' => 'Grade 1', 'code' => 'g1', 'sort_order' => 4],
            ['name' => 'Grade 2', 'code' => 'g2', 'sort_order' => 5],
            ['name' => 'Grade 3', 'code' => 'g3', 'sort_order' => 6],
            ['name' => 'Grade 4', 'code' => 'g4', 'sort_order' => 7],
            ['name' => 'Grade 5', 'code' => 'g5', 'sort_order' => 8],
        ];

        foreach ($levels as $level) {
            SchoolClass::query()->updateOrCreate(
                ['code' => $level['code']],
                ['name' => $level['name'], 'sort_order' => $level['sort_order']]
            );
        }

        $subjectDefs = [
            ['name' => 'English', 'code' => 'english', 'sort_order' => 1],
            ['name' => 'Math', 'code' => 'math', 'sort_order' => 2],
            ['name' => 'Japanese', 'code' => 'japanese', 'sort_order' => 3],
            ['name' => 'Nepali', 'code' => 'nepali', 'sort_order' => 4],
            ['name' => 'Science', 'code' => 'science', 'sort_order' => 5],
            ['name' => 'Art', 'code' => 'art', 'sort_order' => 6],
        ];

        foreach ($subjectDefs as $def) {
            Subject::query()->updateOrCreate(
                ['code' => $def['code']],
                ['name' => $def['name'], 'sort_order' => $def['sort_order']]
            );
        }

        $password = Hash::make('password');

        $teacher = User::query()->updateOrCreate(
            ['email' => 'teacher@example.com'],
            ['name' => 'Demo Teacher', 'password' => $password, 'role' => 'teacher']
        );

        $ukg = SchoolClass::query()->where('code', 'ukg')->first();
        if ($ukg) {
            $ukg->update(['homeroom_teacher_id' => $teacher->id]);
        }

        $student = User::query()->updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Demo Student',
                'password' => $password,
                'role' => 'student',
                'class_id' => $ukg?->id,
                'roll_number' => 'UKG-01',
            ]
        );

        $guardian = User::query()->updateOrCreate(
            ['email' => 'guardian@example.com'],
            ['name' => 'Demo Guardian', 'password' => $password, 'role' => 'guardian']
        );

        User::query()->updateOrCreate(
            ['email' => 'attendance@example.com'],
            ['name' => 'Attendance Operator', 'password' => $password, 'role' => 'attendance']
        );

        User::query()->updateOrCreate(
            ['email' => 'hr@example.com'],
            ['name' => 'Demo HR', 'password' => $password, 'role' => 'hr']
        );

        User::query()->updateOrCreate(
            ['email' => 'finance@example.com'],
            ['name' => 'Demo Finance', 'password' => $password, 'role' => 'finance']
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Demo Staff', 'password' => $password, 'role' => 'staff']
        );

        User::query()->updateOrCreate(
            ['email' => 'other@example.com'],
            ['name' => 'Demo Other', 'password' => $password, 'role' => 'other']
        );

        $tuition = \App\Models\FeeType::query()->updateOrCreate(
            ['code' => 'tuition'],
            [
                'name' => 'Tuition',
                'default_amount' => 50000,
                'currency' => 'JPY',
                'frequency' => 'monthly',
                'description' => 'Monthly tuition fee',
                'is_active' => true,
            ]
        );

        if ($student) {
            \App\Models\Invoice::query()->updateOrCreate(
                ['number' => 'INV-DEMO-0001'],
                [
                    'student_id' => $student->id,
                    'fee_type_id' => $tuition->id,
                    'title' => 'Tuition — September 2026',
                    'amount' => 50000,
                    'currency' => 'JPY',
                    'issue_date' => now()->toDateString(),
                    'due_date' => now()->addDays(14)->toDateString(),
                    'status' => 'issued',
                    'notes' => 'Demo invoice for guardian/student billing views.',
                    'issued_by' => User::query()->where('email', 'finance@example.com')->value('id'),
                ]
            );
        }

        $guardian->wards()->syncWithoutDetaching([
            $student->id => ['relationship' => 'parent'],
        ]);

        if ($ukg) {
            foreach (['english', 'math', 'japanese'] as $code) {
                $subject = Subject::query()->where('code', $code)->first();
                if (! $subject) {
                    continue;
                }

                $course = Course::query()->updateOrCreate(
                    [
                        'class_id' => $ukg->id,
                        'subject_id' => $subject->id,
                        'academic_year' => '2026/2027',
                    ],
                    [
                        'teacher_id' => $teacher->id,
                        'is_active' => true,
                    ]
                );

                Lesson::query()->updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'lesson_date' => now()->toDateString(),
                        'title' => $subject->name.' welcome lesson',
                    ],
                    [
                        'teacher_id' => $teacher->id,
                        'body' => "Today's ".$subject->name." lesson for UKG.\n\nExplore, play, and practice together.",
                        'test_content' => "1) What did we learn today?\n2) Draw one thing from the lesson.",
                        'published_at' => now(),
                    ]
                );
            }
        }
    }
}
