<?php

namespace Zitro\Parameters\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Zitro\Parameters\ParameterServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        // Le indicamos a Orchestra que cargue el Service Provider
        return [
            ParameterServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configuración inicial si fuera necesaria (ej. bases de datos en memoria)
    }
}