<?php

namespace App\Notifications\Channels;

use App\Contracts\NotificationChannelInterface;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

/**
 * Admin notification strategy — dispatches via database channel.
 *
 * Admins receive in-app notifications (stored in the `notifications`
 * table) so they can see them in a dashboard/inbox without email noise.
 */
class AdminNotificationChannel implements NotificationChannelInterface
{
    /**
     * Send a database notification to the given admin user.
     *
     * @param  array<string, mixed>  $data  Notification payload
     */
    public function send(User $user, array $data): void
    {
        $user->notify(
            new \Illuminate\Notifications\Messages\DatabaseMessage($data)
        );
    }
}
