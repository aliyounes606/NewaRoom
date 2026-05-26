<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;


class EloquentArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Get all articles (paginated), newest first.
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return Article::with(['user', 'tags'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a single article by ID with relationships eager-loaded.
     */
    public function find(int $id): ?Article
    {
        return Article::with(['user', 'tags', 'comments.user'])
            ->find($id);
    }

    /**
     * Create a new article.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Article
    {
        return Article::create($data);
    }

    /**
     * Update an existing article.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Article $article, array $data): Article
    {
        $article->update($data);

        return $article->fresh();
    }

    /**
     * Delete an article.
     */
    public function delete(Article $article): bool
    {
        return $article->delete();
    }

    /**
     * Get only published articles (paginated), most recent first.
     */
    public function published(int $perPage = 15): LengthAwarePaginator
    {
        return Article::published()
            ->with(['user', 'tags'])
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Get articles by a specific author (paginated).
     */
    public function byAuthor(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Article::where('user_id', $userId)
            ->with(['tags'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Archive unpublished articles older than the given number of days.
     *
     * @return int Number of articles archived
     */
    public function archive(int $days = 30): int
    {
        return Article::where('status', '!=', 'published')
            ->where('status', '!=', 'archived')
            ->where('created_at', '<=', now()->subDays($days))
            ->update(['status' => 'archived']);
    }
}
