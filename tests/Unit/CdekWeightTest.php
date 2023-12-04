<?php

namespace Tests\Unit;

use App\Services\CDEK\Entities\Weight;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CdekWeightTest extends TestCase
{
    public static function fromKilosProvider(): array
    {
        return [
            ['0.3', 300],
            [0.3, 300],
            [2, 2000],
            [null, 0],
            [0, 0],
        ];
    }

    #[DataProvider('fromKilosProvider')]
    public function testCreatingFromKilos($input, int $expected): void
    {
        $weight = Weight::fromKilos($input);

        $this->assertSame($expected, $weight->jsonSerialize());
    }
}
