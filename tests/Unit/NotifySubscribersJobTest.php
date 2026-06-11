<?php

use App\Jobs\SendArticlePublishedNotification;
use App\Models\Article;


it('job is built with the correct article data', function () {
    $article = Article::factory()->make(['id' => 99, 'user_id' => 1, 'title' => 'Breaking News']);

    $job = new SendArticlePublishedNotification($article);

    expect($job->article->id)->toBe(99);
    expect($job->article->title)->toBe('Breaking News');
});

it('article is not null inside the job', function () {
    $article = Article::factory()->make(['user_id' => 1]);

    $job = new SendArticlePublishedNotification($article);

    expect($job->article)->not->toBeNull();
});
