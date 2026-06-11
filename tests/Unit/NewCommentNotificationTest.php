<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\NewCommentNotification;


it('via() returns mail channel for writer notifiable', function () {
    $writer       = User::factory()->make(['role' => 'writer']);
    $comment      = Comment::factory()->make(['user_id' => 1, 'commentable_id' => 1, 'commentable_type' => App\Models\Article::class]);
    $notification = new NewCommentNotification($comment);

    expect($notification->via($writer))->toBe(['mail']);
});

it('via() returns database channel for admin notifiable', function () {
    $admin        = User::factory()->make(['role' => 'admin']);
    $comment      = Comment::factory()->make(['user_id' => 1, 'commentable_id' => 1, 'commentable_type' => App\Models\Article::class]);
    $notification = new NewCommentNotification($comment);

    expect($notification->via($admin))->toBe(['database']);
});
