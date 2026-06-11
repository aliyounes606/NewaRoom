<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Comment $comment,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * Admins receive database notifications; all others receive mail.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        if ($notifiable->isAdmin()) {
            return ['database'];
        }

        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New comment on your article')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Someone commented on your article.')
            ->line("\"{$this->comment->body}\"")
            ->line('Thank you for using NewsRoom!');
    }

    /**
     * Get the array representation of the notification (database channel).
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'body'       => $this->comment->body,
            'user_id'    => $this->comment->user_id,
            'message'    => 'New comment on your article.',
        ];
    }
}
