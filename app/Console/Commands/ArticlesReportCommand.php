<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command to generate a report of published articles.
 *
 * This command counts the number of published articles per writer
 * for the current month, prints it to the terminal as a table,
 * and saves it to `storage/logs/articles-report.log`.
 * 
 * Signature: `php artisan articles:report`
 */
class ArticlesReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a report of published articles per writer for the current month';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $this->info("Generating articles report for {$startOfMonth->format('F Y')}...");

        // Query to get published articles count per writer for the current month
        $writers = User::where('role', 'writer')
            ->withCount(['articles' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->where('status', 'published')
                      ->whereBetween('published_at', [$startOfMonth, $endOfMonth]);
            }])
            ->get();

        $reportLines = [];
        $reportLines[] = "=== Articles Report: {$startOfMonth->format('F Y')} ===";
        $reportLines[] = "Generated at: " . now()->toIso8601String();
        $reportLines[] = "--------------------------------------------------";
        
        $tableData = [];
        foreach ($writers as $writer) {
            $tableData[] = [
                'Name' => $writer->name,
                'Email' => $writer->email,
                'Published Articles' => $writer->articles_count
            ];
            $reportLines[] = "{$writer->name} ({$writer->email}): {$writer->articles_count} published article(s)";
        }
        
        $reportLines[] = "==================================================";
        
        // Print to terminal
        $this->table(['Name', 'Email', 'Published Articles'], $tableData);
        $this->info("Report saved to storage/logs/articles-report.log.");

        // Log to storage/logs/articles-report.log
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/articles-report.log'),
        ])->info(implode(PHP_EOL, $reportLines));

        return self::SUCCESS;
    }
}
