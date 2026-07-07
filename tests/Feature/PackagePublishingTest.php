<?php

use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Setup & Teardown Helpers
|--------------------------------------------------------------------------
*/

// Helper para limpiar el archivo de configuración clonado
$cleanConfig = function () {
    $target = config_path('parameters.php');
    if (file_exists($target)) {
        unlink($target);
    }
    return $target;
};

// Helper para limpiar las migraciones clonadas
$cleanMigrations = function () {
    $pattern = database_path('migrations/*_create_parameters_table.php');
    foreach (glob($pattern) as $file) {
        unlink($file);
    }
    return $pattern;
};

/*
|--------------------------------------------------------------------------
| Publishing Tests
|--------------------------------------------------------------------------
*/

test('it can publish the configuration file', function () use ($cleanConfig) {
    $targetConfigPath = $cleanConfig();

    expect(file_exists($targetConfigPath))->toBeFalse();

    // Ejecuta el comando de publicación para el tag de configuración
    Artisan::call('vendor:publish', [
        '--tag' => 'parameters-config',
    ]);

    expect(file_exists($targetConfigPath))->toBeTrue();

    // Valida que el contenido sea un array válido del paquete
    $publishedConfig = require $targetConfigPath;
    expect($publishedConfig)->toBeArray()
        ->toHaveKey('table_name');

    $cleanConfig();
});

test('it can publish the migrations', function () use ($cleanMigrations) {
    $pattern = $cleanMigrations();

    expect(glob($pattern))->toBeEmpty();

    // Ejecuta el comando de publicación para el tag de las migraciones
    Artisan::call('vendor:publish', [
        '--tag' => 'parameters-migrations',
    ]);

    $publishedMigrations = glob($pattern);

    expect($publishedMigrations)->toHaveCount(1);
    expect(file_exists($publishedMigrations[0]))->toBeTrue();

    $cleanMigrations();
});