<?php

namespace App\Console\Commands;

use App\Services\ArticleService;
use Illuminate\Console\Command;

/**
 * Command to archive old unpublished articles.
 *
 * This command targets articles that are 'draft' or other non-published,
 * non-archived statuses, and are older than a given number of days.
 * 
 * Signature: `php artisan articles:archive {--days=30}`
 */
class ArchiveArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:archive {--days=30 : Number of days before an unpublished article is archived}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive unpublished articles older than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle(ArticleService $articleService): int
    {
        $days = (int) $this->option('days');
        
        $this->info("Archiving unpublished articles older than {$days} days...");
        
        $archivedCount = $articleService->archiveOld($days);
        
        $this->info("Successfully archived {$archivedCount} article(s).");
        
        return self::SUCCESS;
    }
}
