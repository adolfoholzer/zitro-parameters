<?php

namespace Zitro\Parameters\Services;

use Zitro\Parameters\Enums\ParameterType;
use Zitro\Parameters\Models\Parameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class ParameterService
 *
 * Administra la persistencia, consulta y ciclo de vida de la caché
 * para los parámetros dinámicos del sistema.
 */
class ParameterService
{
    /**
     * Obtiene un parámetro global o asociado a un modelo específico.
     *
     * @param string $key El identificador único del parámetro.
     * @param mixed $default Valor de retorno por defecto si el parámetro no existe.
     * @param \Illuminate\Database\Eloquent\Model|null $model Instancia del modelo si es un parámetro polimórfico.
     * @return mixed El valor del parámetro casteado a su tipo nativo, o el valor por defecto.
     */
    public function get(string $key, mixed $default = null, ?Model $model = null): mixed
    {
        $useCache = config('parameters.use_cache', true);
        $ttl = config('parameters.cache_ttl', 3600);
        $cacheKey = $this->generateCacheKey($key, $model);

        if ($useCache) {
            return Cache::remember($cacheKey, $ttl, function () use ($key, $default, $model) {
                return $this->searchInDatabase($key, $default, $model);
            });
        }

        return $this->searchInDatabase($key, $default, $model);
    }

    /**
     * Guarda o actualiza un parámetro global o asignado a un modelo específico.
     *
     * @param string $key El identificador único del parámetro.
     * @param mixed $value El valor a almacenar. Si es un array se convertirá a JSON de forma automática.
     * @param \App\Zitro\Parameters\Enums\ParameterType|null $tipo El tipo de dato para el casteo.
     * @param string|null $description Breve comentario descriptivo del parámetro.
     * @param \Illuminate\Database\Eloquent\Model|null $model Instancia del modelo dueño del parámetro si aplica.
     * @return \App\Zitro\Parameters\Models\Parameter La instancia del parámetro guardada.
     */
    public function set(
        string $key,
        mixed $value,
        ?ParameterType $type = null,
        ?string $description = null,
        ?Model $model = null
    ): Parameter {
        $valueToSave = is_array($value) ? json_encode($value) : $value;

        $attributes = [
            'parameterable_type' => $model ? $model->getMorphClass() : null,
            'parameterable_id' => $model ? $model->getKey() : null,
            'key' => $key,
        ];

        $values = ['value' => $valueToSave];

        if ($type) {
            $values['type'] = $type;
        }

        if ($description) {
            $values['description'] = $description;
        }

        $parameter = Parameter::updateOrCreate($attributes, $values);

        Cache::forget($this->generateCacheKey($key, $model));

        return $parameter;
    }

    /**
     * Elimina un parámetro específico del sistema y limpia su caché asociada.
     *
     * @param string $key El identificador único del parámetro.
     * @param \Illuminate\Database\Eloquent\Model|null $model Instancia del modelo dueño si no es global.
     * @return bool True si el parámetro se eliminó correctamente, false si no se encontró.
     */
    public function forget(string $key, ?Model $model = null): bool
    {
        $query = Parameter::where('key', $key);

        if ($model) {
            $query->where('parameterable_type', $model->getMorphClass())
                  ->where('parameterable_id', $model->getKey());
        } else {
            $query->whereNull('parameterable_type')
                  ->whereNull('parameterable_id');
        }

        $parameter = $query->first();
        
        if ($parameter) {
            $parameter->delete();
            Cache::forget($this->generateCacheKey($key, $model));

            return true;
        }

        return false;
    }

    /**
     * Helper interno para resolver las consultas directas en la base de datos.
     *
     * @param string $key El identificador único del parámetro.
     * @param mixed $default Valor de retorno por defecto.
     * @param \Illuminate\Database\Eloquent\Model|null $model Instancia del modelo dueño del parámetro.
     * @return mixed El valor del parámetro resuelto o el valor por defecto.
     */
    private function searchInDatabase(string $key, mixed $default, ?Model $model): mixed
    {
        $query = Parameter::where('key', $key);

        if ($model) {
            $query->where('parameterable_type', $model->getMorphClass())
                  ->where('parameterable_id', $model->getKey());
        } else {
            $query->whereNull('parameterable_type')
                  ->whereNull('parameterable_id');
        }

        $parameter = $query->first();

        return $parameter ? $parameter->value : $default;
    }

    /**
     * Genera una key de caché única y estructurada para evitar colisiones.
     *
     * @param string $key El identificador único del parámetro.
     * @param \Illuminate\Database\Eloquent\Model|null $model Instancia del modelo asociado.
     * @return string La key generada para la caché (cache key).
     */
    private function generateCacheKey(string $key, ?Model $model): string
    {
        if ($model) {
            return "parameter.model.{$model->getMorphClass()}.{$model->getKey()}.{$key}";
        }

        return "parameter.global.{$key}";
    }
}