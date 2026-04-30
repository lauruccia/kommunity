<?php

namespace App\Providers;

use App\Models\User;
use App\Notifications\Channels\WebPushChannel;
use App\Observers\UserObserver;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        // ── Channel custom Web Push ──────────────────────────────────────────
        // Le Notification possono usare 'web_push' nell'array via().
        $this->app->make(ChannelManager::class)->extend('web_push', function ($app) {
            return $app->make(WebPushChannel::class);
        });
    }
}
