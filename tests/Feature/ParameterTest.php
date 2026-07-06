<?php

namespace Tests\Feature;

use Tests\TestCase;
use Zitro\Parameters\Facades\ZitroParameters;
use Zitro\Parameters\Enums\ParameterType;
use Zitro\Parameters\ParameterServiceProvider;
use Zitro\Parameters\Traits\HasParameters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TestUser extends Model {
    use HasParameters;
    protected $fillable = ['id'];
    protected $keyType = 'string'; 
}

beforeEach(function () {
    // Registramos el provider en caliente para que el contenedor conozca 'zitro-parameters'
    $this->app->register(ParameterServiceProvider::class);

    // Solo creamos la tabla si no fue creada por la app principal, evitando colisiones
    if (!Schema::hasTable(config('parameters.table_name', 'parameters'))) {
        Schema::create(config('parameters.table_name', 'parameters'), function ($table) {
            $table->id();
            $table->timestamps();
            $table->nullableUuidMorphs('parameterable'); 
            $table->string('key');
            $table->text('value')->nullable();
            $table->enum('type', array_column(ParameterType::cases(), 'value'))->default('string');
            $table->string('description')->nullable();
        });
    }

    Cache::flush();
});

test('corre tests de ParameterTest', function () {
    expect(true)->toBeTrue();
});

// =========================================================================
// SECCIÓN 1: Tests de Parámetros Globales (Fachada)
// =========================================================================

test('puede guardar y recuperar un parámetro global tipo string', function () {
    ZitroParameters::set('site_name', 'Zitro Platform', ParameterType::STRING);

    $valor = ZitroParameters::get('site_name');

    expect($valor)->toBeString()->toBe('Zitro Platform');
});

test('el enum castea correctamente valores numéricos y booleanos globales', function () {
    ZitroParameters::set('empresa_iva', '22.5', ParameterType::FLOAT);
    ZitroParameters::set('maintenance_mode', 'true', ParameterType::BOOLEAN);
    ZitroParameters::set('max_attempts', '5', ParameterType::INT);

    expect(ZitroParameters::get('empresa_iva'))->toBeFloat()->toBe(22.5);
    expect(ZitroParameters::get('maintenance_mode'))->toBeBool()->toBeTrue();
    expect(ZitroParameters::get('max_attempts'))->toBeInt()->toBe(5);
});

test('puede guardar y castear estructuras JSON a arrays de PHP', function () {
    $configJson = ['dark_mode' => true, 'lang' => 'es'];
    
    ZitroParameters::set('ui_settings', $configJson, ParameterType::JSON);

    $resultado = ZitroParameters::get('ui_settings');

    expect($resultado)->toBeArray()
        ->toHaveKey('dark_mode', true)
        ->toHaveKey('lang', 'es');
});

test('retorna el valor por defecto si la clave global no existe', function () {
    $valor = ZitroParameters::get('clave_fantasma', 'valor_default');

    expect($valor)->toBe('valor_default');
});

// =========================================================================
// SECCIÓN 2: Tests Polimórficos (Trait en Modelos)
// =========================================================================

test('un modelo con el trait puede gestionar sus propios parámetros', function () {
    $user = new TestUser(['id' => '9b1a2c3d-fake-uuid']);

    $user->setParameter('user_theme', 'dark', ParameterType::STRING);
    $user->setParameter('items_per_page', '50', ParameterType::INT);

    expect($user->getParameter('user_theme'))->toBe('dark');
    expect($user->getParameter('items_per_page'))->toBeInt()->toBe(50);
});

test('los parámetros de un modelo no colisionan con los globales ni con otros modelos', function () {
    ZitroParameters::set('theme', 'light', ParameterType::STRING);
    
    $user1 = new TestUser(['id' => '1']);
    $user2 = new TestUser(['id' => '2']);

    $user1->setParameter('theme', 'dark', ParameterType::STRING);
    $user2->setParameter('theme', 'cosmic', ParameterType::STRING);

    expect(ZitroParameters::get('theme'))->toBe('light');
    expect($user1->getParameter('theme'))->toBe('dark');
    expect($user2->getParameter('theme'))->toBe('cosmic');
});

// =========================================================================
// SECCIÓN 3: Tests de Caché e Invalidación
// =========================================================================

test('el servicio almacena en caché las lecturas y las invalida al actualizar', function () {
    ZitroParameters::set('cached_param', 'version_1', ParameterType::STRING);

    expect(ZitroParameters::get('cached_param'))->toBe('version_1');

    // Manipulamos la BD de forma directa saltándonos el flujo del service
    DB::table(config('parameters.table_name', 'parameters'))->where('key', 'cached_param')->update(['value' => 'version_hackeada']);

    // Debería seguir saliendo de la caché intacto
    expect(ZitroParameters::get('cached_param'))->toBe('version_1');

    // Al setear mediante el service, limpia la caché quirúrgicamente
    ZitroParameters::set('cached_param', 'version_2', ParameterType::STRING);

    expect(ZitroParameters::get('cached_param'))->toBe('version_2');
});