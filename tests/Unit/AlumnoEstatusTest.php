<?php

namespace Tests\Unit;

use App\Support\AlumnoEstatus;
use PHPUnit\Framework\TestCase;

class AlumnoEstatusTest extends TestCase
{
    public function test_mapea_irregular_desde_importacion_legacy(): void
    {
        $this->assertSame(
            AlumnoEstatus::IRREGULAR,
            AlumnoEstatus::desdeRegistroImport(['es_irregular' => true, 'es_regular' => false])
        );
    }

    public function test_bloquea_acceso_en_bajas(): void
    {
        $this->assertTrue(AlumnoEstatus::bloqueaAccesoPortal(AlumnoEstatus::BAJA_TEMPORAL));
        $this->assertTrue(AlumnoEstatus::bloqueaAccesoPortal(AlumnoEstatus::BAJA_DEFINITIVA));
        $this->assertFalse(AlumnoEstatus::bloqueaAccesoPortal(AlumnoEstatus::REGULAR));
    }
}
