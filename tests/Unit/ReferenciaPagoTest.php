<?php

namespace Tests\Unit;

use App\Support\ReferenciaPago;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReferenciaPagoTest extends TestCase
{
    #[DataProvider('referenciasProvider')]
    public function test_genera_referencia_desde_curp(string $curp, string $esperada): void
    {
        $this->assertSame($esperada, ReferenciaPago::desdeCurp($curp));
    }

    public function test_retorna_null_si_curp_es_corta(): void
    {
        $this->assertNull(ReferenciaPago::desdeCurp('AOCJ03080'));
        $this->assertNull(ReferenciaPago::desdeCurp(null));
        $this->assertNull(ReferenciaPago::desdeCurp(''));
    }

    public static function referenciasProvider(): array
    {
        return [
            ['AOCJ030808MQTCDSA5', 'AOCJ0308080'],
            ['AOSJ050511MQTCLNA9', 'AOSJ0505113'],
            ['GUPK02510MHDFRNN09', 'GUPK02510M1'],
            ['aocj030808mqtcdsa5', 'AOCJ0308080'],
        ];
    }
}
