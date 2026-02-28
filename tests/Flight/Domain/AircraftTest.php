<?php

declare(strict_types=1);

namespace App\Tests\Flight\Domain;

use App\Flight\Domain\ValueObject\Aircraft;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AircraftTest extends TestCase
{
    public function testCreatesValidAircraft(): void
    {
        $aircraft = new Aircraft('Boeing 737', 180);

        $this->assertSame('Boeing 737', $aircraft->getModel());
        $this->assertSame(180, $aircraft->getTotalSeats());
    }

    public function testEquality(): void
    {
        $a = new Aircraft('Boeing 737', 180);
        $b = new Aircraft('Boeing 737', 180);
        $c = new Aircraft('Airbus A320', 150);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testRejectsEmptyModel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Aircraft('', 180);
    }

    public function testRejectsWhitespaceOnlyModel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Aircraft('   ', 180);
    }

    public function testRejectsZeroSeats(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Aircraft('Boeing 737', 0);
    }

    public function testRejectsNegativeSeats(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Aircraft('Boeing 737', -1);
    }
}
