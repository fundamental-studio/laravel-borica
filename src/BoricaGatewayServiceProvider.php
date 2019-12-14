<?php

    namespace Borica;

    use Illuminate\Support\ServiceProvider;

    class BoricaGatewayServiceProvider extends ServiceProvider
    {
        /**
         * Register services.
         *
         * @return void
         */
        public function register()
        {
            //
            $this->mergeConfigFrom(__DIR__ . '/../config/laravel-borica.php', 'laravel-borica');
        }

        /**
         * Bootstrap services.
         *
         * @return void
         */
        public function boot()
        {
            //
            $this->publishes([
                __DIR__ . '/../config/' => config_path('laravel_borica.php'),

                // __DIR__ . '/../resources/views/'
            ], 'laravel-borica');
        }
    }