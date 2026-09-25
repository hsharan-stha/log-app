<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentAttendanceAlert extends Notification
{
    use Queueable;

    public function __construct(
        public User $student,
        public string $action,
        public string $occurredAt,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $class = $this->student->schoolClass?->name;
        $phrase = $this->actionPhrase();

        return (new MailMessage)
            ->subject($this->student->name.' '.$phrase)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->student->name.($class ? " ({$class})" : '')." {$phrase} at {$this->occurredAt}.")
            ->line('This alert was sent from the school attendance system.')
            ->action('Open guardian portal', url('/guardian'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $phrase = $this->actionPhrase();

        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'action' => $this->action,
            'occurred_at' => $this->occurredAt,
            'message' => $this->student->name.' '.$phrase.' at '.$this->occurredAt,
        ];
    }

    private function actionPhrase(): string
    {
        return match ($this->action) {
            'bus_checkin' => 'boarded the school bus',
            'bus_checkout' => 'left on the school bus',
            'school_checkin', 'checkin' => 'checked in at school',
            'school_checkout', 'checkout' => 'checked out from school',
            default => 'updated attendance',
        };
    }
}
