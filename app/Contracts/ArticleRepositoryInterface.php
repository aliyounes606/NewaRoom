<?php

namespace App\Contracts;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for article data access.
 *
 * Controllers depend on this interface — if the data source changes
 * (e.g., from Eloquent to an external API), only the concrete
 * implementation is swapped. Controllers remain untouched.
 */
interface ArticleRepositoryInterface
{
    /**
     * Get all articles (paginated).
     */
    public function all(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a single article by ID.
     */
    public function find(int $id): ?Article;

    /**
     * Create a new article.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Article;

    /**
     * Update an existing article.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Article $article, array $data): Article;

    /**
     * Delete an article.
     */
    public function delete(Article $article): bool;

    /**
     * Get only published articles (paginated).
     */
    public function published(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get articles by a specific author (paginated).
     */
    public function byAuthor(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Archive unpublished articles older than the given number of days.
     *
     * @return int Number of articles archived
     */
    public function archive(int $days = 30): int;
}
