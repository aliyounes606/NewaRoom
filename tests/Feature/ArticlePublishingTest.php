<?php

use App\Events\ArticlePublished;
use App\Jobs\SendArticlePublishedNotification;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;


it('publishing an article dispatches the notification job to the queue', function () {
    Queue::fake();

    $writer  = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create(['user_id' => $writer->id, 'status' => 'draft']);
    Sanctum::actingAs($writer);

    $this->patchJson("/api/articles/{$article->id}/publish");

    Queue::assertPushed(SendArticlePublishedNotification::class);
});

it('publishing an article sends a confirmation email to the writer', function () {
    Mail::fake();

    $writer  = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create(['user_id' => $writer->id, 'status' => 'draft']);
    Sanctum::actingAs($writer);

    $this->patchJson("/api/articles/{$article->id}/publish");

    Mail::assertSent(\App\Mail\ArticlePublishedMail::class, function ($mail) use ($writer) {
        return $mail->hasTo($writer->email);
    });
});

it('creating a draft article does not dispatch a job or send an email', function () {
    Queue::fake();
    Mail::fake();

    $writer = User::factory()->create(['role' => 'writer']);
    Sanctum::actingAs($writer);

    $this->postJson('/api/articles', [
        'title'   => 'Draft Article Title Long Enough',
        'content' => str_repeat('Some content. ', 10),
        'status'  => 'draft',
    ]);

    Queue::assertNotPushed(SendArticlePublishedNotification::class);
    Mail::assertNothingSent();
});

it('the ArticlePublished event is dispatched when an article is published', function () {
    Event::fake();

    $writer  = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create(['user_id' => $writer->id, 'status' => 'draft']);
    Sanctum::actingAs($writer);

    $this->patchJson("/api/articles/{$article->id}/publish");

    Event::assertDispatched(ArticlePublished::class, function ($event) use ($article) {
        return $event->article->id === $article->id;
    });
});
