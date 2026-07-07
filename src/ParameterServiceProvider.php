<?php

namespace Zitro\Parameters;

use Zitro\Parameters\Services\ParameterService;
use Illuminate\Support\ServiceProvider;

/**
 * Class ParameterServiceProvider
 *
 * Se encarga de inicializar el paquete, fusionar configuraciones,
 * registrar el singleton en el contenedor de servicios y publicar los assets.
 */
class ParameterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/parameters.php', 'parameters'
        );

        // Registrar el servicio principal en el contenedor
        $this->app->singleton('zitro-parameters', function ($app) {
            return new ParameterService();
        });

        $this->app->alias('zitro-parameters', ParameterService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__, 1) . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__, 1) . '/config/parameters.php' => config_path('parameters.php'),
            ], 'parameters-config');

            $this->publishes([
                dirname(__DIR__, 1) . '/database/migrations' => database_path('migrations'),
            ], 'parameters-migrations');
        }
    }
}