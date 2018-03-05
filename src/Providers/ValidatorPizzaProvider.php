<?php

namespace romanzipp\ValidatorPizza\Providers;

class ValidatorPizzaProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/../validator-pizza.php' => config_path('validator-pizza.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/../validator-pizza.php', 'validator-pizza'
        );
    }
}
