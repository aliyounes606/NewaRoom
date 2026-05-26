<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Phase 7: Maintenance Scheduled Commands ──────────────────────────
Schedule::command('articles:archive --days=30')->monthlyOn(1, '00:00');
Schedule::command('articles:report')->weeklyOn(5, '08:00');
