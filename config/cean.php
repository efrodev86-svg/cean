<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identidad CEAN
    |--------------------------------------------------------------------------
    |
    | CEAN = Control Escolar y Administración Normalista
    |
    */

    'acronym' => env('CEAN_ACRONYM', 'CEAN'),

    'full_name' => env('CEAN_FULL_NAME', 'Control Escolar y Administración Normalista'),

    'title' => env('CEAN_TITLE', 'CEAN — Control Escolar y Administración Normalista'),

    'institution' => env('CEAN_INSTITUTION', 'Escuela Normal Superior de Querétaro'),

    'tagline' => env('CEAN_TAGLINE', 'Consulta boletas y gestión de calificaciones.'),

    /*
    |--------------------------------------------------------------------------
    | Dominio institucional (UI y restricción OAuth opcional)
    |--------------------------------------------------------------------------
    */

    'institutional_email_domain' => env('CEAN_INSTITUTIONAL_DOMAIN', 'ensq.edu.mx'),

    /*
    |--------------------------------------------------------------------------
    | Tipos de contratación docente
    |--------------------------------------------------------------------------
    */

    'tipos_contratacion_docente' => [
        'base' => 'Base',
        'interinato' => 'Interinato',
        'honorarios' => 'Honorarios',
        'asignatura' => 'Por asignatura',
        'otro' => 'Otro',
    ],

];
