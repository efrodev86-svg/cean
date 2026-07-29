<?php

namespace Tests\Unit;

use App\Support\AlumnoEstadoCivil;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AlumnoEstadoCivilTest extends TestCase
{
    #[DataProvider('valoresProvider')]
    public function test_normaliza_estado_civil(?string $entrada, ?string $esperado): void
    {
        $this->assertSame($esperado, AlumnoEstadoCivil::normalizar($entrada));
    }

    public static function valoresProvider(): array
    {
        return [
            [null, null],
            ['', null],
            ['Soltero(a)', AlumnoEstadoCivil::SOLTERO],
            ['Soltero (a)', AlumnoEstadoCivil::SOLTERO],
            ['Casado (a)', AlumnoEstadoCivil::CASADO],
            ['Viudo(a)', AlumnoEstadoCivil::VIUDO],
            ['Unión Libre/Concubinato', AlumnoEstadoCivil::CONCUBINATO],
            ['Concubinato', AlumnoEstadoCivil::CONCUBINATO],
            ['otro', null],
        ];
    }
}
