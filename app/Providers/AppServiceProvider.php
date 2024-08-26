<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Src\Domain\DiscountSystem\DiscountSystemService;

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
        Model::shouldBeStrict();

        app()->when(DiscountSystemService::class)
            ->needs('$config')
            ->giveConfig('setup.discount_levels');
    }
}
