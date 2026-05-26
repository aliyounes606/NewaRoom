<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\User;
use App\Notifications\ArticlePublishedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;


class SendArticlePublishedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     * Exponential backoff: 10s → 20s → 40s.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 20, 40];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Article $article,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->article->loadMissing('user');

        $recipients = User::whereIn('role', ['reader', 'admin'])->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new ArticlePublishedNotification($this->article));
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error(
            'SendArticlePublishedNotification failed',
            [
                'article_id' => $this->article->id ?? null,
                'error' => $exception?->getMessage(),
            ]
        );
    }
}
