<?php

namespace App\Listeners;

use App\Events\ArticlePublished;
use App\Jobs\SendArticlePublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;


class NotifySubscribers implements ShouldQueue
{
    /**
     * The name of the queue the listener should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle the event.
     */
    public function handle(ArticlePublished $event): void
    {
        SendArticlePublishedNotification::dispatch($event->article);
    }
}
