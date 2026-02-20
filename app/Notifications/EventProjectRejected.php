<?php

namespace App\Notifications;

use App\Models\EventProject;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventProjectRejected extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public EventProject $eventProject, public string $rejectionReason = '')
    {
        //
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
        $user = User::find($this->eventProject->verified_by);
        return new BroadcastMessage([
            'link' => route('event-projects.show', $this->eventProject->id),
            'title' => 'Penolakan Kegiatan',
            'message' => 'Kegiatan ' . $this->eventProject->event_name . ' yang dibuat telah ditolak oleh ' . $user->name,
            'type' => 'error',
        ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Event Project Rejection')
            ->markdown('mail.event-rejected', ['eventProject' => $this->eventProject, 'rejectionReason' => $this->rejectionReason]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $user = User::find($this->eventProject->verified_by);
        return [
            'link' => route('event-projects.show', $this->eventProject->id),
            'title' => 'Penolakan Kegiatan',
            'message' => 'Kegiatan ' . $this->eventProject->event_name . ' yang dibuat telah ditolak oleh ' . $user->name,
            'reason' => $this->rejectionReason,
        ];
    }
}
