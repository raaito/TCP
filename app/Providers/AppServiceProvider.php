<?php

namespace App\Providers;

use App\Services\Contracts\FleetServiceInterface;
use App\Services\Contracts\GeofenceServiceInterface;
use App\Services\Contracts\TripServiceInterface;
use App\Services\FleetService;
use App\Services\GeofenceService;
use App\Services\TripService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TripServiceInterface::class, TripService::class);
        $this->app->bind(GeofenceServiceInterface::class, GeofenceService::class);
        $this->app->bind(FleetServiceInterface::class, FleetService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
