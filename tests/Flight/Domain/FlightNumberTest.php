<?php

declare(strict_types=1);

namespace App\Tests\Flight\Domain;

use App\Flight\Domain\Exception\InvalidFlightNumberException;
use App\Flight\Domain\ValueObject\FlightNumber;
use PHPUnit\Framework\TestCase;

class FlightNumberTest extends TestCase
{
    public function testCreatesValidFlightNumber(): void
    {
        $number = new FlightNumber('LO123');

        $this->assertSame('LO123', $number->getValue());
        $this->assertSame('LO123', (string) $number);
    }

    public function testNormalizesToUppercase(): void
    {
        $number = new FlightNumber('lo123');

        $this->assertSame('LO123', $number->getValue());
    }

    public function testTrimsPadding(): void
    {
        $number = new FlightNumber(' FR4567 ');

        $this->assertSame('FR4567', $number->getValue());
    }

    public function testEquality(): void
    {
        $a = new FlightNumber('LO123');
        $b = new FlightNumber('LO123');
        $c = new FlightNumber('FR456');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testAcceptsOneDigit(): void
    {
        $number = new FlightNumber('LO1');

        $this->assertSame('LO1', $number->getValue());
    }

    public function testAcceptsFourDigits(): void
    {
        $number = new FlightNumber('FR4567');

        $this->assertSame('FR4567', $number->getValue());
    }

    /**
     * @dataProvider invalidFlightNumbers
     */
    public function testRejectsInvalidFlightNumber(string $number): void
    {
        $this->expectException(InvalidFlightNumberException::class);

        new FlightNumber($number);
    }

    /** @return array<string, array{string}> */
    public static function invalidFlightNumbers(): array
    {
        return [
            'three letters (LOT123)' => ['LOT123'],
            'one letter'             => ['L123'],
            'no digits'              => ['LO'],
            'five digits'            => ['LO12345'],
            'empty string'           => [''],
            'only digits'            => ['1234'],
            'special chars'          => ['LO-123'],
        ];
    }
}
