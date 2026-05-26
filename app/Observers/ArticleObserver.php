<?php

namespace App\Observers;

use App\Models\Article;
use App\Services\DashboardCacheService;

/**
 * Observes Article model lifecycle events for cache invalidation.
 */
class ArticleObserver
{
    /**
     * Create the observer instance.
     */
    public function __construct(
        protected DashboardCacheService $cacheService,
    ) {}

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        $this->cacheService->invalidateStats();
        $this->cacheService->invalidateTags();
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        $this->cacheService->invalidateStats();
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        $this->cacheService->invalidateStats();
        $this->cacheService->invalidateTags();
    }
}
