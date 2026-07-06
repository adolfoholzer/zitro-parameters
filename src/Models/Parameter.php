<?php

namespace Zitro\Parameters\Models;

use Zitro\Parameters\Enums\ParameterType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model Parameter
 *
 * Representa la entidad encargada de almacenar los parámetros
 * del sistema, soportando asignaciones globales o polimórficas.
 */
class Parameter extends Model
{
    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parameterable_type',
        'parameterable_id',
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => ParameterType::class,
    ];

    /**
     * Obtiene el nombre de la tabla asociada al modelo desde la configuración.
     *
     * @return string El nombre de la tabla de la base de datos.
     */
    public function getTable(): string
    {
        return config('parameters.table_name', 'parameters');
    }

    /**
     * Relación polimórfica hacia el modelo dueño del parámetro (ej: User, Team, etc.).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function parameterable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accesor para el atributo 'value'.
     *
     * Delega el casteo del string crudo directamente al método del Enum ParameterType.
     *
     * @param mixed $value El valor crudo de la base de datos.
     * @return mixed El valor transformado según su tipo configurado.
     */
    public function getValueAttribute($value): mixed
    {
        return $this->type->cast($value);
    }
}