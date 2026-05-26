<?php

namespace App\Notifications\Channels;

use App\Contracts\NotificationChannelInterface;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Writer notification strategy — dispatches via email channel.
 *
 * Writers receive email notifications for events like article
 * publishing, keeping them informed without requiring them to
 * log into the system.
 */
class WriterNotificationChannel implements NotificationChannelInterface
{
    /**
     * Send an email notification to the given writer user.
     *
     * @param  array<string, mixed>  $data  Notification payload
     */
    public function send(User $user, array $data): void
    {
        Mail::raw(
            $data['message'] ?? 'You have a new notification.',
            fn ($message) => $message
                ->to($user->email)
                ->subject($data['subject'] ?? 'NewsRoom Notification')
        );
    }
}
