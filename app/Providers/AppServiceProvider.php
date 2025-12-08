<?php

namespace App\Providers;

use App\Interface\DeviceTokenInterface;
use App\Repository\DeviceTokenRepository;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\Fcm\FcmChannel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DeviceTokenInterface::class, DeviceTokenRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->make(ChannelManager::class)->extend('fcm', function ($app) {
            return new FcmChannel(
                $app->make(\Illuminate\Contracts\Events\Dispatcher::class),
                $app->make(\Kreait\Firebase\Contract\Messaging::class)
            );
        });
    }
}
