<?php

namespace Tests\Unit;

use App\Support\CalificacionEnLetra;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CalificacionEnLetraTest extends TestCase
{
    #[DataProvider('enterosProvider')]
    public function test_convierte_calificacion_entera_a_letra(float $calificacion, string $esperado): void
    {
        $this->assertSame($esperado, CalificacionEnLetra::entero($calificacion));
    }

    #[DataProvider('decimalesProvider')]
    public function test_convierte_calificacion_decimal_a_letra(float $calificacion, string $esperado): void
    {
        $this->assertSame($esperado, CalificacionEnLetra::decimal($calificacion));
    }

    public static function enterosProvider(): array
    {
        return [
            [9, 'NUEVE'],
            [10, 'DIEZ'],
            [6, 'SEIS'],
        ];
    }

    public static function decimalesProvider(): array
    {
        return [
            [9.0, 'NUEVE PUNTO CERO'],
            [8.5, 'OCHO PUNTO CINCO'],
        ];
    }
}
