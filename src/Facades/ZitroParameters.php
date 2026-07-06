<?php

namespace Zitro\Parameters\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static \App\Zitro\Parameters\Models\Parameter set(string $key, mixed $value, ?\App\Zitro\Parameters\Enums\ParameterType $type = null, ?string $descripcion = null)
 * @method static bool forget(string $key)
 * * @see Zitro\Parameters\Services\ParameterService
 */
class ZitroParameters extends Facade
{
    /**
     * Obtiene el nombre registrado del componente en el contenedor de servicios.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'zitro-parameters';
    }
}