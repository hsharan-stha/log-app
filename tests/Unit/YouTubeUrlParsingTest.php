<?php

namespace Tests\Unit;

use App\Models\Lesson;
use Tests\TestCase;

class YouTubeUrlParsingTest extends TestCase
{
    public function test_parses_common_youtube_url_formats(): void
    {
        $cases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/live/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s' => 'dQw4w9WgXcQ',
        ];

        foreach ($cases as $url => $expected) {
            $this->assertSame($expected, Lesson::parseYouTubeVideoId($url), $url);
        }
    }

    public function test_non_youtube_urls_return_null(): void
    {
        $this->assertNull(Lesson::parseYouTubeVideoId('https://meet.google.com/abc-defg-hij'));
        $this->assertNull(Lesson::parseYouTubeVideoId(null));
        $this->assertNull(Lesson::parseYouTubeVideoId(''));
    }

    public function test_lesson_builds_embed_url(): void
    {
        $lesson = new Lesson([
            'recording_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);

        $this->assertTrue($lesson->isYouTubeRecording());
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $lesson->youtubeEmbedUrl());
    }
}
