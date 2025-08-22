<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OrderService;
use App\Services\ProductionService;
use App\Services\ProductProductionService;
use App\Services\RecipeService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductProductionService::class, function ($app) {
            return new ProductProductionService();
        });
        
        $this->app->bind(ProductionService::class, function ($app) {
            return new ProductionService();
        });
        $this->app->bind(RecipeService::class, function ($app) {
            return new RecipeService();
        });
        $this->app->bind(OrderService::class, function ($app) {
            return new OrderService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
