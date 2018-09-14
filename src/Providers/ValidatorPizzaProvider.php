<?php

namespace romanzipp\ValidatorPizza\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use romanzipp\ValidatorPizza\Rules\DisposableEmailPizza as ValidatorRule;

class ValidatorPizzaProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/../config/validator-pizza.php' => config_path('validator-pizza.php'),
        ], 'config');

        $this->loadMigrationsFrom(
            dirname(__DIR__) . '/../migrations'
        );

        Validator::extend('disposable_pizza', ValidatorRule::class . '@passes');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/../config/validator-pizza.php', 'validator-pizza'
        );
    }
}
