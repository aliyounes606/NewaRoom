<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a new article is published.
 *
 * Supports both `mail` and `database` channels. This notification is
 * dispatched asynchronously by SendArticlePublishedNotification job
 * (Phase 4) and will later be triggered by the ArticlePublished event
 * via the NotifySubscribers listener (Phase 5).
 *
 * Queue-friendly: implements ShouldQueue so it doesn't block HTTP responses.
 */
class ArticlePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Article $article,
    ) {
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
            ->subject("New Article Published: {$this->article->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A new article has been published on NewsRoom.")
            ->line("**{$this->article->title}**")
            ->line($this->getExcerpt())
            ->action('Read Article', url("/articles/{$this->article->slug}"))
            ->line('Thank you for being a valued reader!');
    }

    /**
     * Get the array representation of the notification (database channel).
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'article_id'   => $this->article->id,
            'title'        => $this->article->title,
            'slug'         => $this->article->slug,
            'author'       => $this->article->user?->name ?? 'Unknown',
            'published_at' => $this->article->published_at?->toIso8601String(),
            'message'      => "New article published: {$this->article->title}",
        ];
    }

    /**
     * Generate a short excerpt from the article content.
     */
    private function getExcerpt(): string
    {
        $plainText = strip_tags($this->article->content ?? '');

        return strlen($plainText) > 150
            ? substr($plainText, 0, 150) . '…'
            : $plainText;
    }
}
