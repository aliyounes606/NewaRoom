<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome notification sent to newly created users.
 *
 * Dispatched by UserObserver::created(). Queue-friendly — implements
 * ShouldQueue so email delivery does not block user creation.
 *
 * Supports both mail and database channels.
 */
class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to NewsRoom!')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Welcome to NewsRoom — your source for the latest articles and insights.')
            ->line("Your account has been created with the **{$notifiable->role}** role.")
            ->action('Visit NewsRoom', url('/'))
            ->line('We\'re glad to have you on board!');
    }

    /**
     * Get the array representation of the notification (database channel).
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'message' => "Welcome to NewsRoom, {$notifiable->name}!",
            'role'    => $notifiable->role,
        ];
    }
}
