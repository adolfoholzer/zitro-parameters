<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Table Name
    |--------------------------------------------------------------------------
    |
    | Aquí se define el nombre de la tabla que utilizará el paquete para
    | almacenar y consultar los parámetros del sistema en la base de datos.
    |
    */

    'table_name' => 'parameters',

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Determina si se deben almacenar en caché las lecturas de los parámetros
    | para evitar consultas repetitivas a la base de datos, mejorando el
    | rendimiento general de la aplicación.
    |
    */

    'use_cache' => env('ZITRO_PARAMETERS_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Time to Live (TTL)
    |--------------------------------------------------------------------------
    |
    | Define el tiempo de vida en segundos que los parámetros permanecerán
    | almacenados en la caché antes de expirar y volver a consultar la BD.
    | Por defecto es 3600 segundos (1 hora).
    |
    */

    'cache_ttl' => 3600,

];