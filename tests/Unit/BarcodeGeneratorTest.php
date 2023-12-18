<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Src\Domain\Synchronizer\Services\BarcodeGenerator;

class BarcodeGeneratorTest extends TestCase
{
    public static function ean13(): array
    {
        return [
            ['123456789012', false],
            [3017028393660, true],
            ['3017028393660', true],
            ['OZN1091926491', false],
            ['1234567890123', false],
            ['12345678901234', false],
            ['12345678b123', false],
            ['12345я78д123', false],
        ];
    }

    #[DataProvider('ean13')]
    public function testEan13Check($input, bool $expected): void
    {
        $result = BarcodeGenerator::isEan13($input);

        $this->assertSame($expected, $result);
    }
}
