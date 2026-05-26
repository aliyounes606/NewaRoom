<?php

namespace App\Listeners;

use App\Events\ArticlePublished;
use App\Services\DashboardCacheService;


class InvalidateDashboardCache
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected DashboardCacheService $cacheService,
    ) {
    }

    /**
     * Handle the event.
     *
     * Invalidates dashboard stats cache. Publishing an article changes
     * the published count and potentially the most-active-writers ranking.
     */
    public function handle(ArticlePublished $event): void
    {
        $this->cacheService->invalidateStats();
    }
}
