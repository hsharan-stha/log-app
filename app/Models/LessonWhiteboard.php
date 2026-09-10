<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LessonWhiteboard extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'path',
        'sort_order',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
