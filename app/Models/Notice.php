<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'body',
        'audience',
        'class_id',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->published_at === null || $this->published_at->isFuture()) {
            return $user->isAdmin() || $this->author_id === $user->id;
        }

        return match ($this->audience) {
            'all' => true,
            'teachers' => $user->isTeacher() || $user->isAdmin(),
            'students' => $user->isStudent() || $user->isAdmin(),
            'guardians' => $user->isGuardian() || $user->isAdmin(),
            'class' => $this->visibleToClassMember($user),
            default => $user->isAdmin(),
        };
    }

    private function visibleToClassMember(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStudent()) {
            return (int) $user->class_id === (int) $this->class_id;
        }

        if ($user->isTeacher()) {
            return SchoolClass::query()
                ->where('id', $this->class_id)
                ->where('homeroom_teacher_id', $user->id)
                ->exists();
        }

        if ($user->isGuardian()) {
            return $user->wards()
                ->where('class_id', $this->class_id)
                ->exists();
        }

        return false;
    }
}
