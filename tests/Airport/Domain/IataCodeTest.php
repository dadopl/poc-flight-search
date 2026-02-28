<?php

declare(strict_types=1);

namespace App\Tests\Airport\Domain;

use App\Airport\Domain\IataCode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class IataCodeTest extends TestCase
{
    public function testCreatesValidIataCode(): void
    {
        $code = new IataCode('KTW');

        $this->assertSame('KTW', $code->getValue());
        $this->assertSame('KTW', (string) $code);
    }

    public function testNormalizesToUppercase(): void
    {
        $code = new IataCode('ktw');

        $this->assertSame('KTW', $code->getValue());
    }

    public function testTrimsPadding(): void
    {
        $code = new IataCode(' WAW ');

        $this->assertSame('WAW', $code->getValue());
    }

    public function testEquality(): void
    {
        $a = new IataCode('KTW');
        $b = new IataCode('KTW');
        $c = new IataCode('WAW');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    /**
     * @dataProvider invalidCodes
     */
    public function testRejectsInvalidCode(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);

        new IataCode($code);
    }

    /** @return array<string, array{string}> */
    public static function invalidCodes(): array
    {
        return [
            'too short'     => ['KT'],
            'too long'      => ['KTWA'],
            'with digit'    => ['K1W'],
            'empty string'  => [''],
            'special chars' => ['K-W'],
        ];
    }
}
