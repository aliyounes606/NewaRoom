<?php

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\Events\ArticlePublished;
use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Article orchestration service.
 */
class ArticleService
{
    public function __construct(
        protected ArticleRepositoryInterface $repository,
    ) {}

    /**
     * Get all articles (paginated).
     */
    public function listAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($perPage);
    }

    /**
     * Find a single article by ID.
     */
    public function findById(int $id): ?Article
    {
        return $this->repository->find($id);
    }

    /**
     * Get only published articles (paginated).
     */
    public function listPublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->published($perPage);
    }

    /**
     * Get articles by a specific author (paginated).
     */
    public function listByAuthor(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->byAuthor($userId, $perPage);
    }

    /**
     * Create a new article.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int>  $tagIds
     */
    public function create(array $data, array $tagIds = []): Article
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        $article = $this->repository->create($data);

        if (! empty($tagIds)) {
            $article->tags()->sync($tagIds);
        }

        return $article->load(['user', 'tags']);
    }

    /**
     * Update an existing article.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int>|null  $tagIds
     */
    public function update(Article $article, array $data, ?array $tagIds = null): Article
    {
        if (isset($data['title']) && $data['title'] !== $article->title) {
            $data['slug'] = Str::slug($data['title']);
        }

        $article = $this->repository->update($article, $data);

        if ($tagIds !== null) {
            $article->tags()->sync($tagIds);
        }

        return $article->load(['user', 'tags']);
    }

    /**
     * Delete an article.
     */
    public function delete(Article $article): bool
    {
        return $this->repository->delete($article);
    }

    /**
     * Publish an article.
     *
     * ArticlePublished fires only on first transition to published.
     */
    public function publish(Article $article): Article
    {
        $wasAlreadyPublished = $article->status === 'published';

        $data = [
            'status' => 'published',
            'published_at' => $article->published_at ?? now(),
        ];

        $article = $this->repository->update($article, $data);

        if (! $wasAlreadyPublished) {
            ArticlePublished::dispatch($article);
        }

        return $article;
    }

    /**
     * Archive unpublished articles older than N days.
     */
    public function archiveOld(int $days = 30): int
    {
        return $this->repository->archive($days);
    }
}
