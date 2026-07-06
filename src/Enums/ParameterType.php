<?php

namespace Zitro\Parameters\Enums;

use Carbon\Carbon;

/**
 * Enum ParameterType
 *
 * Define los tipos de datos soportados para el casteo dinámico
 * de los parámetros del sistema.
 */
enum ParameterType: string
{
    case STRING = 'string';
    case INT = 'int';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case JSON = 'json';

    /**
     * Transforma un valor string crudo de la base de datos al tipo de dato nativo de PHP.
     *
     * @param mixed $value El valor original almacenado.
     * @return mixed El valor transformado a su tipo nativo correspondiente o null.
     */
    public function cast(mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return match ($this) {
            self::STRING  => (string) $value,
            self::INT     => (int) $value,
            self::FLOAT   => (float) $value,
            self::BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::DATE    => Carbon::parse($value),
            self::JSON    => is_array($value) ? $value : json_decode($value, true),
        };
    }
}