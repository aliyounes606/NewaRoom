<?php

use App\Models\Article;
use App\Models\User;
use Laravel\Sanctum\Sanctum;


it('article list endpoint returns response with data and meta keys', function () {
    Article::factory()->count(3)->create(['status' => 'published']);

    $this->getJson('/api/v1/articles')
         ->assertOk()
         ->assertJsonStructure([
             'data' => [
                 '*' => ['id', 'title', 'content', 'author_name', 'published_at'],
             ],
             'links',
             'meta' => ['current_page', 'total', 'per_page'],
         ]);
});

it('article detail endpoint does not expose password or sensitive user data', function () {
    $user    = User::factory()->create(['role' => 'reader']);
    $article = Article::factory()->create(['status' => 'published']);
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/articles/{$article->id}")
         ->assertOk()
         ->assertJsonMissing(['password'])
         ->assertJsonMissing(['remember_token'])
         ->assertJsonMissing(['email_verified_at']);
});

it('V2 article endpoint includes reading_time, comments_count, and tags', function () {
    $user    = User::factory()->create(['role' => 'reader']);
    $article = Article::factory()->create(['status' => 'published']);
    Sanctum::actingAs($user);

    $this->getJson("/api/v2/articles/{$article->id}")
         ->assertOk()
         ->assertJsonStructure([
             'data' => ['id', 'title', 'content', 'author_name', 'published_at', 'reading_time', 'comments_count', 'tags'],
         ]);
});

it('V1 article endpoint does not include reading_time or comments_count', function () {
    $user    = User::factory()->create(['role' => 'reader']);
    $article = Article::factory()->create(['status' => 'published']);
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/articles/{$article->id}")
         ->assertOk()
         ->assertJsonMissing(['reading_time'])
         ->assertJsonMissing(['comments_count']);
});
