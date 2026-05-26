<?php

namespace App\Contracts;

use App\Models\User;

/**
 * Strategy interface for notification dispatch.
 *
 * Admins receive database notifications; Writers receive emails.
 * Same event, different behavior per context — resolved via
 * Laravel's Contextual Binding in RepositoryServiceProvider.
 */
interface NotificationChannelInterface
{
    /**
     * Send a notification to the given user.
     *
     * @param  array<string, mixed>  $data  Notification payload
     */
    public function send(User $user, array $data): void;
}
