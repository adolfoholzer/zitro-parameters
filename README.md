# Zitro Parameters Package

![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

Paquete de gestión de parámetros dinámicos para Laravel que permite almacenar configuraciones tipadas tanto a nivel global del sistema como asociadas de forma polimórfica a cualquier modelo de la aplicación.

Diseñado para ser simple, extensible, desacoplado y fácilmente testeable.

---

# ✨ Características

* Parámetros globales para configuraciones del sistema
* Parámetros asociados a cualquier modelo mediante relaciones polimórficas
* Conversión automática de tipos nativos
* Soporte para `string`, `int`, `float`, `boolean` y `array/json`
* Caché integrada para optimizar consultas frecuentes
* Compatible con IDs tradicionales y UUIDs
* Trait de integración rápida para modelos Eloquent
* Facade lista para usar
* Testing integrado con Pest

---

# 📦 Instalación

## 1. Ejecutar Migraciones

El paquete registra automáticamente sus migraciones.

Ejecuta:

```bash
php artisan migrate
```

---

## 2. Publicar Configuración (Opcional)

Si deseas personalizar el nombre de la tabla o el comportamiento de la caché:

```bash
php artisan vendor:publish --tag="zitro-parameters-config"
```

---

# ⚙️ Configuración

Archivo publicado:

```php
config/parameters.php
```

Configuración por defecto:

```php
return [

    'table_name' => 'parameters',

    'use_cache' => env('ZITRO_PARAMETERS_CACHE', true),

    'cache_ttl' => 3600,

];
```

---

# 🚀 Uso Básico

El paquete permite trabajar con dos tipos de parámetros:

* Parámetros globales del sistema
* Parámetros asociados a modelos específicos

Todos los valores son almacenados tipados y recuperados automáticamente con su tipo nativo correspondiente.

---

# 🌐 Parámetros Globales

Para administrar configuraciones globales utiliza la Facade:

```php
use ZitroParameters;
use Zitro\Parameters\Enums\ParameterType;
```

---

## Guardar Parámetros

```php
ZitroParameters::set(
    'site_iva',
    22.5,
    ParameterType::FLOAT,
    'IVA general del comercio'
);

ZitroParameters::set(
    'maintenance_mode',
    true,
    ParameterType::BOOLEAN
);

ZitroParameters::set(
    'allowed_countries',
    ['UY', 'AR', 'BR'],
    ParameterType::JSON
);
```

---

## Obtener Parámetros

Los valores son retornados automáticamente con su tipo correspondiente.

```php
$iva = ZitroParameters::get('site_iva');
// float(22.5)

$maintenance = ZitroParameters::get('maintenance_mode');
// bool(true)

$countries = ZitroParameters::get('allowed_countries');
// ['UY', 'AR', 'BR']
```

---

## Valores por Defecto

```php
$logo = ZitroParameters::get(
    'site_logo',
    'default-logo.png'
);
```

---

## Eliminar Parámetros

```php
ZitroParameters::forget('site_iva');
```

La caché asociada será invalidada automáticamente.

---

# 🧩 Parámetros Asociados a Modelos

Puedes almacenar configuraciones específicas para cualquier entidad de tu sistema.

Por ejemplo:

* Usuarios
* Equipos
* Clientes
* Proyectos
* Organizaciones

---

## Preparar un Modelo

Agregar el trait `HasParameters`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Zitro\Parameters\Traits\HasParameters;

class Team extends Model
{
    use HasParameters;
}
```

---

## Guardar Parámetros

```php
use Zitro\Parameters\Enums\ParameterType;

$team = Team::find($id);

$team->setParameter(
    'max_users',
    15,
    ParameterType::INT,
    'Límite de usuarios contratados'
);

$team->setParameter(
    'modules_enabled',
    ['crm', 'billing'],
    ParameterType::JSON
);
```

---

## Obtener Parámetros

```php
$maxUsers = $team->getParameter('max_users');
// int(15)

$modules = $team->getParameter('modules_enabled');
// ['crm', 'billing']
```

---

## Eliminar Parámetros

```php
$team->forgetParameter('max_users');
```

---

# 🧠 Tipos Soportados

El paquete incluye soporte nativo para:

| Tipo    | Valor Devuelto |
| ------- | -------------- |
| STRING  | string         |
| INT     | int            |
| FLOAT   | float          |
| BOOLEAN | bool           |
| JSON    | array          |

La conversión se realiza automáticamente utilizando el tipo definido al almacenar el parámetro.

---

# ⚡ Sistema de Caché

Para minimizar consultas repetidas a la base de datos, el paquete incorpora una capa de caché transparente.

Características:

* Caché independiente para parámetros globales
* Caché segmentada para parámetros polimórficos
* Invalidación automática al actualizar valores
* TTL configurable

Configuración:

```php
'use_cache' => true,
'cache_ttl' => 3600,
```

---

# 🔬 Testing

El paquete incluye pruebas unitarias e integración utilizando Pest.

Ejecutar todas las pruebas:

```bash
vendor/bin/pest
```

Ejecutar únicamente las pruebas del paquete:

```bash
vendor/bin/pest Zitro/Parameter/tests
```