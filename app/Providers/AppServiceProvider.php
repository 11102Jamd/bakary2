<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OrderService;
use App\services\PdfService;
use App\Services\ProductionService;
use App\Services\ProductProductionService;
use App\Services\RecipeService;
use App\Services\SaleService;

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
        $this->app->bind(SaleService::class, function ($app) {
            return new SaleService();
        });
        $this->app->bind(PdfService::class, function ($app) {
            return new PdfService();
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
