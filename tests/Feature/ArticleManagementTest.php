<?php

use App\Models\Article;
use App\Models\User;
use Laravel\Sanctum\Sanctum;


describe('Article access', function () {
    it('returns 401 for unauthenticated create request', function () {
        $this->postJson('/api/articles', [
            'title'   => 'Some Article Title Here',
            'content' => 'Some content body here.',
        ])->assertUnauthorized();
    });

    it('returns 200 for authenticated user listing articles', function () {
        $user = User::factory()->create(['role' => 'reader']);
        Sanctum::actingAs($user);
        $this->getJson('/api/articles')->assertOk();
    });

    it('returns 403 when reader tries to create article', function () {
        $reader = User::factory()->create(['role' => 'reader']);
        Sanctum::actingAs($reader);
        $this->postJson('/api/articles', ['title' => 'Test', 'content' => 'Content'])
             ->assertForbidden();
    });
});

describe('Article creation', function () {
    it('writer creates article successfully and data is persisted', function () {
        $writer = User::factory()->create(['role' => 'writer']);
        Sanctum::actingAs($writer);

        $response = $this->postJson('/api/articles', [
            'title'   => 'My First Article For Writing',
            'content' => str_repeat('Article body content here. ', 10),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('articles', [
            'user_id' => $writer->id,
        ]);
    });

    it('returns 422 when writer submits article without required fields', function () {
        $writer = User::factory()->create(['role' => 'writer']);
        Sanctum::actingAs($writer);

        $this->postJson('/api/articles', [])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['title', 'content']);
    });
});

describe('Article deletion', function () {
    it('admin can delete an article', function () {
        $admin   = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/articles/{$article->id}")->assertOk();
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    });
});
