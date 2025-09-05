<?php

namespace App\Providers;

use App\Services\OrderService;
use App\Services\PdfService;
use App\Services\ProductionService;
use App\Services\ProductProductionService;
use App\Services\RecipeService;
use App\Services\SaleService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService();
        });
        $this->app->singleton(PdfService::class, function ($app) {
            return new PdfService();
        });
        $this->app->singleton(ProductionService::class, function ($app) {
            return new ProductionService();
        });
        $this->app->singleton(ProductProductionService::class, function ($app) {
            return new ProductProductionService();
        });
        $this->app->singleton(RecipeService::class, function ($app){
            return new RecipeService();
        });
        // $this->app->singleton(SaleService::class, function ($app){
        //     return new SaleService();
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
