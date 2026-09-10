<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LessonAttachment extends Model
{
    protected $fillable = [
        'lesson_id',
        'original_name',
        'path',
        'mime_type',
        'kind',
        'size',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function isImage(): bool
    {
        return $this->kind === 'image';
    }

    public function isPdf(): bool
    {
        return $this->kind === 'pdf';
    }
}
