<?php

use App\Models\Article;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;


it('writer can upload an attachment to an article and gets 201', function () {
    Storage::fake('local');

    $writer  = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create(['user_id' => $writer->id]);
    Sanctum::actingAs($writer);

    $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    $this->postJson("/api/articles/{$article->id}/attachments", [
        'file' => $file,
    ])->assertCreated();
});

it('the uploaded file is saved to the correct storage disk', function () {
    Storage::fake('local');

    $writer  = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create(['user_id' => $writer->id]);
    Sanctum::actingAs($writer);

    $file = UploadedFile::fake()->create('report.pdf', 200, 'application/pdf');

    $this->postJson("/api/articles/{$article->id}/attachments", ['file' => $file]);

    $attachment = Attachment::latest()->first();
    Storage::disk('local')->assertExists($attachment->file_path);
});

it('attachment record is persisted in the database linked to the article', function () {
    Storage::fake('local');

    $writer  = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create(['user_id' => $writer->id]);
    Sanctum::actingAs($writer);

    $file = UploadedFile::fake()->create('image.png', 100, 'image/png');

    $this->postJson("/api/articles/{$article->id}/attachments", ['file' => $file]);

    $this->assertDatabaseHas('attachments', [
        'attachable_type' => App\Models\Article::class,
        'attachable_id'   => $article->id,
    ]);
});
