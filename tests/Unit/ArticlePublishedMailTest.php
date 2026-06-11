<?php

use App\Mail\ArticlePublishedMail;
use App\Models\Article;
use App\Models\User;


it('mail is built with the correct subject', function () {
    $article = Article::factory()->make([
        'user_id' => 1,
        'title'   => 'My Great Article',
    ]);

    $mail = new ArticlePublishedMail($article);

    $mail->assertHasSubject('Your article has been published');
});

it('mail is addressed to the correct writer', function () {
    $writer  = User::factory()->make(['email' => 'writer@example.com', 'role' => 'writer']);
    $article = Article::factory()->make(['user_id' => 1, 'title' => 'Test Article Title']);
    $article->setRelation('user', $writer);

    $mail = new ArticlePublishedMail($article);
    $mail->to('writer@example.com');

    $mail->assertTo('writer@example.com');
});
