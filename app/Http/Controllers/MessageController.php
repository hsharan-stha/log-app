<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $inbox = Message::query()
            ->with(['sender', 'student'])
            ->where('recipient_id', $user->id)
            ->latest()
            ->paginate(20, ['*'], 'inbox');

        $sent = Message::query()
            ->with(['recipient', 'student'])
            ->where('sender_id', $user->id)
            ->latest()
            ->paginate(20, ['*'], 'sent');

        $recipients = $this->allowedRecipients($user);

        return view('messages.index', [
            'inbox' => $inbox,
            'sent' => $sent,
            'recipients' => $recipients,
            'layout' => $this->layoutFor($user),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $allowedIds = $this->allowedRecipients($user)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $data = $request->validate([
            'recipient_id' => ['required', 'integer', Rule::in($allowedIds)],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        if (! empty($data['student_id'])) {
            $student = User::query()->findOrFail($data['student_id']);
            abort_unless($student->isStudent(), 422);
            $this->assertStudentAccess($user, $student);
        }

        Message::query()->create([
            'sender_id' => $user->id,
            'recipient_id' => $data['recipient_id'],
            'student_id' => $data['student_id'] ?? null,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Message sent.');
    }

    public function markRead(Request $request, Message $message): RedirectResponse
    {
        abort_unless($message->recipient_id === $request->user()->id, 403);
        $message->markRead();

        return back();
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function allowedRecipients(User $user)
    {
        if ($user->isAdmin() || $user->isHr()) {
            return User::query()
                ->whereIn('role', ['teacher', 'guardian', 'student'])
                ->orderBy('name')
                ->get();
        }

        if ($user->isFinance() || $user->isOfficeStaff() || $user->isOther()) {
            return User::query()
                ->whereIn('role', ['admin', 'hr', 'guardian'])
                ->orderBy('name')
                ->get();
        }

        if ($user->isTeacher()) {
            $classIds = $user->homeroomClasses()->pluck('id');
            $students = User::query()
                ->where('role', 'student')
                ->whereIn('class_id', $classIds)
                ->pluck('id');

            $guardians = User::query()
                ->where('role', 'guardian')
                ->whereHas('wards', fn ($q) => $q->whereIn('users.id', $students))
                ->orderBy('name')
                ->get();

            $admins = User::query()->where('role', 'admin')->orderBy('name')->get();

            return $guardians->merge($admins)->unique('id')->values();
        }

        if ($user->isGuardian()) {
            $wardIds = $user->wards()->pluck('users.id');
            $teacherIds = \App\Models\SchoolClass::query()
                ->whereIn('id', User::query()->whereIn('id', $wardIds)->pluck('class_id'))
                ->pluck('homeroom_teacher_id')
                ->filter();

            return User::query()
                ->where(function ($q) use ($teacherIds) {
                    $q->whereIn('id', $teacherIds)->orWhere('role', 'admin');
                })
                ->orderBy('name')
                ->get();
        }

        // Students: message own guardians + homeroom teacher + admin
        $ids = $user->guardians()->pluck('users.id');
        if ($user->schoolClass?->homeroom_teacher_id) {
            $ids->push($user->schoolClass->homeroom_teacher_id);
        }

        return User::query()
            ->where(function ($q) use ($ids) {
                $q->whereIn('id', $ids)->orWhere('role', 'admin');
            })
            ->orderBy('name')
            ->get();
    }

    private function assertStudentAccess(User $user, User $student): void
    {
        if ($user->isAdmin() || $user->isHr()) {
            return;
        }
        if ($user->isGuardian() && $user->wards()->where('users.id', $student->id)->exists()) {
            return;
        }
        if ($user->isTeacher()) {
            $ok = $user->homeroomClasses()->where('id', $student->class_id)->exists();
            abort_unless($ok, 403);

            return;
        }
        abort(403);
    }

    private function layoutFor(User $user): string
    {
        return match (true) {
            $user->isAdmin(), $user->isHr() => 'layouts.admin',
            $user->isFinance() => 'layouts.finance',
            default => 'layouts.portal',
        };
    }
}
