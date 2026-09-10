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
        $verb = $this->action === 'checkin' ? 'checked in' : 'checked out';
        $class = $this->student->schoolClass?->name;

        return (new MailMessage)
            ->subject($this->student->name.' '.$verb.' at school')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->student->name.($class ? " ({$class})" : '')." {$verb} at {$this->occurredAt}.")
            ->line('This alert was sent from the school attendance kiosk.')
            ->action('Open guardian portal', url('/guardian'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'action' => $this->action,
            'occurred_at' => $this->occurredAt,
            'message' => $this->student->name.' '.($this->action === 'checkin' ? 'checked in' : 'checked out').' at '.$this->occurredAt,
        ];
    }
}
