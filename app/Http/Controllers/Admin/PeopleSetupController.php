<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class PeopleSetupController extends Controller
{
    public function index(): View
    {
        $studentCount = User::query()->where('role', 'student')->count();
        $guardianCount = User::query()->where('role', 'guardian')->count();
        $linkedStudents = User::query()
            ->where('role', 'student')
            ->whereHas('guardians')
            ->count();

        $steps = [
            [
                'step' => 1,
                'title' => 'Students',
                'blurb' => 'Add each child with class and roll number.',
                'count' => $studentCount,
                'ready' => $studentCount > 0,
                'index' => route('admin.students.index'),
                'create' => route('admin.students.create'),
                'create_label' => 'Add student',
            ],
            [
                'step' => 2,
                'title' => 'Guardians',
                'blurb' => 'Create parents/guardians and link them to students.',
                'count' => $guardianCount,
                'ready' => $guardianCount > 0,
                'locked' => $studentCount === 0,
                'index' => route('admin.guardians.index'),
                'create' => route('admin.guardians.create'),
                'create_label' => 'Add guardian',
            ],
        ];

        return view('admin.people.index', [
            'steps' => $steps,
            'studentCount' => $studentCount,
            'guardianCount' => $guardianCount,
            'linkedStudents' => $linkedStudents,
            'canLinkGuardians' => $studentCount > 0,
        ]);
    }
}
