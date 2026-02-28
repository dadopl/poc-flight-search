<?php

declare(strict_types=1);

namespace App\Tests\Airport\Domain;

use App\Airport\Domain\AirportName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AirportNameTest extends TestCase
{
    public function testCreatesValidAirportName(): void
    {
        $name = new AirportName('Katowice Airport');

        $this->assertSame('Katowice Airport', $name->getValue());
        $this->assertSame('Katowice Airport', (string) $name);
    }

    public function testTrimsPadding(): void
    {
        $name = new AirportName('  Warsaw Chopin Airport  ');

        $this->assertSame('Warsaw Chopin Airport', $name->getValue());
    }

    public function testAcceptsNameAtMaxLength(): void
    {
        $name = new AirportName(str_repeat('A', 100));

        $this->assertSame(100, mb_strlen($name->getValue()));
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AirportName('');
    }

    public function testRejectsWhitespaceOnlyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AirportName('   ');
    }

    public function testRejectsNameExceedingMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AirportName(str_repeat('A', 101));
    }
}
