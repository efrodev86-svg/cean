<?php

return [

    'escuela' => env('BOLETA_ESCUELA', 'ESCUELA NORMAL SUPERIOR DE QUERÉTARO'),

    'sistema_educativo' => env(
        'BOLETA_SISTEMA_EDUCATIVO',
        'PERTENECIENTE AL SISTEMA EDUCATIVO ESTATAL'
    ),

    'director' => env('BOLETA_DIRECTOR', 'MTRO. ROBERTO COMPEÁN MARTÍNEZ'),

    'ciudad' => env('BOLETA_CIUDAD', 'SANTIAGO DE QUERÉTARO, QRO.'),

    'licenciatura' => env(
        'BOLETA_LICENCIATURA',
        'LICENCIATURA EN ENSEÑANZA Y APRENDIZAJE EN'
    ),

    'codigo_formulario' => env('BOLETA_CODIGO', 'FM.ENCE.14'),

    'version_formulario' => env('BOLETA_VERSION', '00'),

    'calificacion_minima' => 6,

    'calificacion_maxima' => 10,

    'banner_gobierno' => env('BOLETA_BANNER_GOBIERNO', 'images/boleta/gob-educacion.png'),

    'logo_ensq' => env('BOLETA_LOGO_ENSQ', 'images/boleta/logo-ensq.png'),

    'sello_escuela' => env('BOLETA_SELLO_ESCUELA', 'Esc. Nor. Sup. de Qro.'),

    'sello_clave' => env('BOLETA_SELLO_CLAVE', '22DNL0001P'),

    'sello_direccion' => env('BOLETA_SELLO_DIRECCION', ''),

];
