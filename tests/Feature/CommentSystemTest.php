<?php

use App\Models\Article;
use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;


it('reader can add a comment to a published article and gets 201', function () {
    $reader  = User::factory()->create(['role' => 'reader']);
    $article = Article::factory()->create(['status' => 'published']);
    Sanctum::actingAs($reader);

    $this->postJson("/api/articles/{$article->id}/comments", [
        'body' => 'Great article!',
    ])->assertCreated();
});

it('comment is stored with the correct article and user associations', function () {
    $reader  = User::factory()->create(['role' => 'reader']);
    $article = Article::factory()->create(['status' => 'published']);
    Sanctum::actingAs($reader);

    $this->postJson("/api/articles/{$article->id}/comments", ['body' => 'Nice!']);

    $this->assertDatabaseHas('comments', [
        'commentable_type' => App\Models\Article::class,
        'commentable_id'   => $article->id,
        'user_id'          => $reader->id,
        'body'             => 'Nice!',
    ]);
});

it('article owner receives a notification when a comment is added', function () {
    Notification::fake();

    $owner   = User::factory()->create(['role' => 'writer']);
    $reader  = User::factory()->create(['role' => 'reader']);
    $article = Article::factory()->create(['user_id' => $owner->id, 'status' => 'published']);
    Sanctum::actingAs($reader);

    $this->postJson("/api/articles/{$article->id}/comments", ['body' => 'Interesting!']);

    Notification::assertSentTo($owner, NewCommentNotification::class);
});

it('the reader who wrote the comment does not receive the notification', function () {
    Notification::fake();

    $owner   = User::factory()->create(['role' => 'writer']);
    $reader  = User::factory()->create(['role' => 'reader']);
    $article = Article::factory()->create(['user_id' => $owner->id, 'status' => 'published']);
    Sanctum::actingAs($reader);

    $this->postJson("/api/articles/{$article->id}/comments", ['body' => 'Cool!']);

    Notification::assertNotSentTo($reader, NewCommentNotification::class);
});
