<?php

namespace App\Providers;

use App\Vk\VkApiClient;
use Illuminate\Support\ServiceProvider;

class VkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(VkApiClient::class, function ($app) {
            $token = config('services.vk.token');
            $version = config('services.vk.version');

            return new VkApiClient($token, $version);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
