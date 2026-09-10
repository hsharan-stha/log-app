<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'teacher_id',
        'title',
        'body',
        'test_content',
        'lesson_date',
        'recording_url',
        'published_at',
        'email_sent',
    ];

    protected function casts(): array
    {
        return [
            'lesson_date' => 'date',
            'published_at' => 'datetime',
            'email_sent' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LessonAttachment::class);
    }

    public function whiteboards(): HasMany
    {
        return $this->hasMany(LessonWhiteboard::class)->orderBy('sort_order')->orderBy('id');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function publish(): void
    {
        if ($this->published_at === null) {
            $this->forceFill(['published_at' => now()])->save();
        }
    }

    public function hasRecording(): bool
    {
        return filled($this->recording_url);
    }

    public function liveJoinUrl(): ?string
    {
        if (! $this->hasRecording()) {
            return null;
        }

        return $this->youtubeWatchUrl() ?: $this->recording_url;
    }

    public function livePlatformLabel(): string
    {
        if ($this->isYouTubeRecording()) {
            return 'YouTube';
        }

        $url = strtolower((string) $this->recording_url);
        if (str_contains($url, 'meet.google.com')) {
            return 'Google Meet';
        }
        if (str_contains($url, 'zoom.us') || str_contains($url, 'zoom.com')) {
            return 'Zoom';
        }

        return 'Live class';
    }

    public function isYouTubeRecording(): bool
    {
        return $this->youtubeVideoId() !== null;
    }

    /**
     * Extract a YouTube video ID from watch, youtu.be, shorts, live, or embed URLs.
     */
    public function youtubeVideoId(): ?string
    {
        return static::parseYouTubeVideoId($this->recording_url);
    }

    public function youtubeEmbedUrl(): ?string
    {
        $id = $this->youtubeVideoId();

        return $id ? 'https://www.youtube.com/embed/'.$id : null;
    }

    public function youtubeWatchUrl(): ?string
    {
        $id = $this->youtubeVideoId();

        return $id ? 'https://www.youtube.com/watch?v='.$id : $this->recording_url;
    }

    public static function parseYouTubeVideoId(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (preg_match('~(?:youtube\.com/watch\?(?:.*&)?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/|youtube\.com/live/)([A-Za-z0-9_-]{6,})~i', $url, $matches)) {
            return $matches[1];
        }

        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        if (! str_contains($host, 'youtube.com') && ! str_contains($host, 'youtu.be')) {
            return null;
        }

        if (str_contains($host, 'youtu.be')) {
            $path = ltrim($parts['path'] ?? '', '/');

            return $path !== '' ? explode('/', $path)[0] : null;
        }

        parse_str($parts['query'] ?? '', $query);
        if (! empty($query['v']) && is_string($query['v'])) {
            return $query['v'];
        }

        return null;
    }
}
