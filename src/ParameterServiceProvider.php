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
            __DIR__ . '/../config/parameters.php',
            'parameters'
        );

        // Registramos el servicio tanto por su alias de string como por el FQCN de la clase
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
        // Carga automática de migraciones al ejecutar 'php artisan migrate' en el proyecto principal
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            // Publicar el archivo de configuración
            $this->publishes([
                __DIR__ . '/../config/parameters.php' => config_path('parameters.php'),
            ], 'parameters-config');

            // Opcional: Permitir al usuario publicar las migraciones físicas en su proyecto si desea editarlas
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'parameters-migrations');
        }
    }
}