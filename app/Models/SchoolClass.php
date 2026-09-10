<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sort_order',
        'homeroom_teacher_id',
    ];

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class, 'class_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'class_id');
    }
}
