<?php

namespace App\Notifications;

use App\Models\Lesson;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LessonPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(public Lesson $lesson) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lesson = $this->lesson->loadMissing(['course.subject', 'course.schoolClass']);
        $courseName = $lesson->course?->displayName() ?? 'Course';

        return (new MailMessage)
            ->subject('New lesson: '.$lesson->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line("A new lesson was published for {$courseName}.")
            ->line($lesson->lesson_date?->toFormattedDateString().' — '.$lesson->title)
            ->action('View lesson', url('/student/courses/'.$lesson->course_id.'/lessons/'.$lesson->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lesson_id' => $this->lesson->id,
            'course_id' => $this->lesson->course_id,
            'title' => $this->lesson->title,
            'message' => 'New lesson: '.$this->lesson->title,
        ];
    }
}
