<?php

namespace App\Notifications;

use App\Models\EventProject;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventProjectCreated extends Notification
{
    use Queueable;

    public $creator;
    public $eventProject;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, EventProject $eventProject)
    {
        $this->creator = $user;
        $this->eventProject = $eventProject;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'link' => route('event-projects.show', $this->eventProject->id),
            'title' => 'Kegiatan Baru Dibuat',
            'message' => 'Ada kegiatan baru yang dibuat oleh ' . $this->creator->name,
            'status' => 'info',
        ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kegiatan Baru Dibuat')
            ->markdown('mail.event-created', ['eventProject' => $this->eventProject, 'creator' => $this->creator]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'link' => route('event-projects.show', $this->eventProject->id),
            'title' => 'Kegiatan Baru Dibuat',
            'message' => 'Ada kegiatan baru yang dibuat oleh ' . $this->creator->name,
        ];
    }
}
