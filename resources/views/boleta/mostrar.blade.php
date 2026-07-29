<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boleta — {{ $alumno->matricula }}</title>
    <link rel="stylesheet" href="{{ asset('css/boleta.css') }}">
</head>
<body class="boleta-body">
    <div class="boleta-toolbar no-print">
        <button type="button" onclick="window.print()">Imprimir boleta</button>
        <a href="{{ route('boleta.index') }}">Nueva consulta</a>
    </div>

    @php
        $sede = $sede ?? null;
        $bannerGobierno = config('boleta.banner_gobierno');
        $logoEnsq = $sede?->logo ?: config('boleta.logo_ensq');
        $escuelaBoleta = $sede?->escuela ?: config('boleta.escuela');
        $directorBoleta = $sede?->director ?: config('boleta.director');
        $tieneBanner = $bannerGobierno && file_exists(public_path($bannerGobierno));
        $tieneLogoEnsq = $logoEnsq && file_exists(public_path($logoEnsq));
    @endphp

    <article class="boleta-pagina">
        {{-- Logos: Secretaría (izq.) · ENSQ (der.) --}}
        <div class="boleta-logos">
            <div class="boleta-logos-izq">
                @if ($tieneBanner)
                    <img src="{{ asset($bannerGobierno) }}" alt="Secretaría de Educación — Querétaro">
                @endif
            </div>
            <div class="boleta-logos-der">
                @if ($tieneLogoEnsq)
                    <img src="{{ asset($logoEnsq) }}" alt="ENSQ">
                @endif
            </div>
        </div>

        {{-- Encabezado --}}
        <header class="boleta-titulos">
            <p class="boleta-escuela">{{ $escuelaBoleta }}</p>
            <p class="boleta-doc-titulo">Boleta de calificaciones</p>
            <p class="boleta-doc-subtitulo">Control escolar</p>
            <p class="boleta-sistema">{{ config('boleta.sistema_educativo') }}, certifica que el (la) c.</p>
        </header>

        {{-- Nombre entre líneas --}}
        <div class="boleta-nombre-bloque">
            <hr class="linea">
            <p class="boleta-nombre-alumno">{{ $alumno->nombreFormal() }}</p>
            <hr class="linea">
        </div>

        {{-- Párrafo académico --}}
        <p class="boleta-parrafo-academico">
            Cursó y acreditó las asignaturas del
            <strong>semestre {{ $alumno->semestreParImpar() }}</strong>,
            correspondiente al plan de estudios de la
            <strong>{{ config('boleta.licenciatura') }} {{ mb_strtoupper($alumno->licenciatura) }}</strong>,
            con las calificaciones finales que se anotan a continuación:
        </p>

        {{-- Matrícula · Semestre-grupo --}}
        <div class="boleta-datos-grid">
            <div class="boleta-campo">
                <span class="etiqueta">Matrícula:</span>
                <span class="valor">{{ $alumno->matricula }}</span>
            </div>
            <div class="boleta-campo">
                <span class="etiqueta">Semestre- grupo:</span>
                <span class="valor">{{ $alumno->semestreGrupo() }}</span>
            </div>
        </div>

        {{-- Tabla de calificaciones --}}
        @if ($calificaciones->isNotEmpty())
            <table class="boleta-tabla-calificaciones">
                <thead>
                    <tr>
                        <th class="col-materia">Materia</th>
                        <th class="col-ciclo">Ciclo</th>
                        <th class="col-calif">Calif.</th>
                        <th class="col-letra">Letra</th>
                        <th class="col-asistencia">% de asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calificaciones as $calificacion)
                        <tr>
                            <td class="celda-materia">{{ $calificacion->materia->nombre }}</td>
                            <td>{{ $ciclo->nombre }}</td>
                            <td>
                                {{ number_format($calificacion->calificacion, $calificacion->calificacion == (int) $calificacion->calificacion ? 0 : 1) }}
                            </td>
                            <td>{{ \App\Support\CalificacionEnLetra::entero($calificacion->calificacion) }}</td>
                            <td>{{ $calificacion->asistencia_porcentaje }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if ($promedio !== null)
                    <tfoot>
                        <tr>
                            <td colspan="2" class="celda-promedio-etiqueta">Promedio :</td>
                            <td class="celda-promedio-num">{{ number_format($promedio, 1) }}</td>
                            <td colspan="2" class="celda-promedio-letra">{{ $promedioLetra }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        @else
            <p class="boleta-sin-calificaciones">No hay calificaciones publicadas para este semestre.</p>
        @endif

        {{-- Texto legal --}}
        <p class="boleta-legal">
            La escala de calificaciones es de 0 al {{ config('boleta.calificacion_maxima') }};
            la mínima aprobatoria es de {{ config('boleta.calificacion_minima') }}.
            Este documento ampara las asignaturas del plan de estudios en vigor.
            En cumplimiento de las prescripciones legales se extiende el presente en la ciudad de
            <span class="boleta-legal-fecha">{{ $fechaDestacada }}</span>
        </p>

        {{-- Firma director --}}
        <div class="boleta-pie-firmas">
            <div class="boleta-firma-director">
                <div class="boleta-firma-espacio" aria-hidden="true"></div>
                <div class="boleta-firma-linea"></div>
                <p class="boleta-firma-nombre">{{ $directorBoleta }}</p>
                <p class="boleta-firma-cargo">Director</p>
            </div>
        </div>

        <div class="boleta-codigo">
            {{ config('boleta.codigo_formulario') }}<br>
            Versión {{ config('boleta.version_formulario') }}
        </div>
    </article>
</body>
</html>
