<?php

namespace App\Providers;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\NotificationChannelInterface;
use App\Notifications\Channels\AdminNotificationChannel;
use App\Notifications\Channels\WriterNotificationChannel;
use App\Repositories\EloquentArticleRepository;
use Illuminate\Support\ServiceProvider;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            ArticleRepositoryInterface::class,
            EloquentArticleRepository::class,
        );

        $this->app->when(AdminNotificationChannel::class)
            ->needs(NotificationChannelInterface::class)
            ->give(AdminNotificationChannel::class);

        $this->app->when(WriterNotificationChannel::class)
            ->needs(NotificationChannelInterface::class)
            ->give(WriterNotificationChannel::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
