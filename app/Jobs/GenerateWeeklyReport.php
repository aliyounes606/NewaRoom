<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\User;
use App\Notifications\WeeklyReportNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;


class GenerateWeeklyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     * Exponential backoff: 30s → 60s → 120s.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     *
     * Collects weekly article stats and sends a report notification
     * to all admin users.
     */
    public function handle(): void
    {
        $periodEnd = Carbon::now();
        $periodStart = $periodEnd->copy()->subWeek();

        $reportData = $this->buildReport($periodStart, $periodEnd);

        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new WeeklyReportNotification($reportData));
    }

    /**
     * Build the weekly report data.
     *
     * @return array{
     *     total_published: int,
     *     total_draft: int,
     *     top_writers: array,
     *     period_start: string,
     *     period_end: string,
     * }
     */
    private function buildReport(Carbon $periodStart, Carbon $periodEnd): array
    {
        $totalPublished = Article::where('status', 'published')
            ->whereBetween('published_at', [$periodStart, $periodEnd])
            ->count();

        $totalDraft = Article::where('status', 'draft')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        $topWriters = User::where('role', 'writer')
            ->withCount([
                'articles' => function ($query) use ($periodStart, $periodEnd) {
                    $query->where('status', 'published')
                        ->whereBetween('published_at', [$periodStart, $periodEnd]);
                }
            ])
            ->having('articles_count', '>', 0)
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

        return [
            'total_published' => $totalPublished,
            'total_draft' => $totalDraft,
            'top_writers' => $topWriters,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error(
            'GenerateWeeklyReport failed',
            [
                'error' => $exception?->getMessage(),
            ]
        );
    }
}
