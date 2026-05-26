<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class DashboardCacheService
{
    /**
     * Cache TTL in seconds (10 minutes).
     */
    private const STATS_TTL = 600;

    private const TAGS_TTL = 600;

    private const LOCK_TIMEOUT = 10;

    private const LOCK_WAIT = 5;

    /**
     * Get all dashboard data (stats + trending tags) in one call.
     *
     * @return array{stats: array, trending_tags: array}
     */
    public function getDashboardData(): array
    {
        return [
            'stats' => $this->getStats(),
            'trending_tags' => $this->getTrendingTags(),
        ];
    }

    /**
     * Get cached dashboard statistics.
     *
     * @return array{total_articles: int, total_comments: int, most_active_writers: array}
     */
    public function getStats(): array
    {
        $cached = Cache::get('dashboard:stats');

        if ($cached !== null) {
            return $cached;
        }

        $lock = Cache::lock('dashboard-stats-rebuild', self::LOCK_TIMEOUT);

        try {
            if ($lock->block(self::LOCK_WAIT)) {
                $cached = Cache::get('dashboard:stats');
                if ($cached !== null) {
                    return $cached;
                }

                $stats = $this->buildStats();
                Cache::put('dashboard:stats', $stats, self::STATS_TTL);

                return $stats;
            }
        } finally {
            $lock->release();
        }

        return $this->buildStats();
    }

    /**
     * Get cached trending tags.
     *
     * @return array
     */
    public function getTrendingTags(): array
    {
        $cached = Cache::get('dashboard:trending-tags');

        if ($cached !== null) {
            return $cached;
        }

        $lock = Cache::lock('dashboard-tags-rebuild', self::LOCK_TIMEOUT);

        try {
            if ($lock->block(self::LOCK_WAIT)) {
                $cached = Cache::get('dashboard:trending-tags');
                if ($cached !== null) {
                    return $cached;
                }

                $tags = $this->buildTrendingTags();
                Cache::put('dashboard:trending-tags', $tags, self::TAGS_TTL);

                return $tags;
            }
        } finally {
            $lock->release();
        }

        return $this->buildTrendingTags();
    }

    /**
     * Invalidate dashboard stats cache only.
     */
    public function invalidateStats(): void
    {
        Cache::forget('dashboard:stats');
    }

    /**
     * Invalidate trending tags cache only.
     */
    public function invalidateTags(): void
    {
        Cache::forget('dashboard:trending-tags');
    }

    /**
     * Build dashboard stats from the database.
     *
     * @return array{total_articles: int, total_comments: int, most_active_writers: array}
     */
    private function buildStats(): array
    {
        return [
            'total_articles' => Article::count(),
            'total_comments' => Comment::count(),
            'most_active_writers' => $this->getMostActiveWriters(),
        ];
    }

    /**
     * Get the top 5 most active writers by article count.
     *
     * @return array
     */
    private function getMostActiveWriters(): array
    {
        return User::where('role', 'writer')
            ->withCount('articles')
            ->orderByDesc('articles_count')
            ->limit(5)
            ->get(['id', 'name', 'email'])
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'articles_count' => $user->articles_count,
            ])
            ->toArray();
    }

    /**
     * Build trending tags from the database.
     *
     * @return array
     */
    private function buildTrendingTags(): array
    {
        return Tag::withCount('articles as usage_count')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get(['id', 'name', 'slug'])
            ->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'usage_count' => $tag->usage_count,
            ])
            ->toArray();
    }
}
