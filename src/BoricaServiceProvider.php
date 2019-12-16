<?php

    namespace Fundamental\Borica;

    use Illuminate\Support\ServiceProvider;

    class BoricaServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap services.
         *
         * @return void
         */
        public function boot()
        {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-borica.php'),
            ], 'config');
        }

        /**
         * Register services.
         *
         * @return void
         */
        public function register()
        {
            $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-borica');

            config([
                'config/laravel-borica.php'
            ]);
        }
    }