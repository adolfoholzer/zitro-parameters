<?php

namespace Zitro\Parameters\Traits;

use Zitro\Parameters\Enums\ParameterType;
use Zitro\Parameters\Facades\ZitroParameters;
use Zitro\Parameters\Models\Parameter;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait HasParameters
 *
 * Permite a cualquier modelo de Eloquent gestionar parámetros dinámicos
 * de forma polimórfica, integrándose automáticamente con el sistema de caché.
 */
trait HasParameters
{
    /**
     * Relación polimórfica nativa de muchos a muchos (MorphMany).
     *
     * Permite realizar carga previa (eager loading) desde la aplicación principal
     * utilizando estructuras como: $modelo->load('parameters') o Model::with('parameters').
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function parameters(): MorphMany
    {
        return $this->morphMany(Parameter::class, 'parameterable');
    }

    /**
     * Obtiene el valor de un parámetro específico delegando en el servicio con su caché.
     *
     * @param string $key El identificador único del parámetro.
     * @param mixed $default Valor de retorno por defecto si el parámetro no existe.
     * @return mixed El valor del parámetro casteado a su tipo nativo, o el valor por defecto.
     */
    public function getParameter(string $key, mixed $default = null): mixed
    {
        return ZitroParameters::get($key, $default, $this);
    }

    /**
     * Guarda o actualiza un parámetro para este modelo y limpia su caché asociada.
     *
     * @param string $key El identificador único del parámetro.
     * @param mixed $value El valor a almacenar (acepta arrays para formato JSON).
     * @param \App\Zitro\Parameters\Enums\ParameterType|null $tipo El tipo de dato para el casteo.
     * @param string|null $descripcion Breve comentario descriptivo del parámetro.
     * @return \App\Zitro\Parameters\Models\Parameter La instancia del parámetro guardada.
     */
    public function setParameter(
        string $key,
        mixed $value,
        ?ParameterType $type = null,
        ?string $description = null
    ): Parameter {
        return ZitroParameters::set($key, $value, $type, $description, $this);
    }

    /**
     * Elimina un parámetro específico de este modelo y limpia su caché asociada.
     *
     * @param string $key El identificador único del parámetro.
     * @return bool True si el parámetro se eliminó correctamente, false en caso contrario.
     */
    public function forgetParameter(string $key): bool
    {
        return ZitroParameters::forget($key, $this);
    }
}