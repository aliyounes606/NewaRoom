<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\WelcomeNotification;


class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * Sends a WelcomeNotification to the newly created user.
     * The notification implements ShouldQueue, so it is dispatched
     * to the queue and does not block the HTTP response.
     */
    public function created(User $user): void
    {
        $user->notify(new WelcomeNotification());
    }
}
