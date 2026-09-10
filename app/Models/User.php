<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'class_id',
        'roll_number',
        'face_descriptor',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'face_descriptor',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'face_descriptor' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /** @deprecated Prefer isOfficeStaff() for role=staff, or isTeacher() for teachers. */
    public function isStaff(): bool
    {
        return $this->isTeacher();
    }

    public function isOfficeStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isGuardian(): bool
    {
        return $this->role === 'guardian';
    }

    public function isAttendance(): bool
    {
        return $this->role === 'attendance';
    }

    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    public function isOther(): bool
    {
        return $this->role === 'other';
    }

    public function canManageAcademicsAndPeople(): bool
    {
        return $this->isAdmin() || $this->isHr();
    }

    public function canManageBilling(): bool
    {
        return $this->isAdmin() || $this->isFinance();
    }

    public function canUseFaceAttendance(): bool
    {
        return $this->isTeacher() || $this->isStudent();
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'student_id');
    }

    public function wards(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'guardian_student', 'guardian_id', 'student_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'guardian_student', 'student_id', 'guardian_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function authoredNotices(): HasMany
    {
        return $this->hasMany(Notice::class, 'author_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'hr' => 'admin.academics.index',
            'finance' => 'finance.dashboard',
            'teacher' => 'teacher.dashboard',
            'student' => 'student.dashboard',
            'guardian' => 'guardian.dashboard',
            'attendance' => 'attendance.home',
            'staff', 'other' => 'office.dashboard',
            default => 'login',
        };
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Principal',
            'hr' => 'HR',
            'finance' => 'Finance',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'guardian' => 'Guardian',
            'attendance' => 'Attendance',
            'staff' => 'Staff',
            'other' => 'Other',
            default => ucfirst((string) $this->role),
        };
    }
}
