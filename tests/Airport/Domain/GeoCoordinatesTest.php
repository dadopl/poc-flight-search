<?php

declare(strict_types=1);

namespace App\Tests\Airport\Domain;

use App\Airport\Domain\GeoCoordinates;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GeoCoordinatesTest extends TestCase
{
    public function testCreatesValidGeoCoordinates(): void
    {
        $coords = new GeoCoordinates(50.4744, 19.0800);

        $this->assertSame(50.4744, $coords->getLatitude());
        $this->assertSame(19.0800, $coords->getLongitude());
    }

    public function testAcceptsBoundaryLatitude(): void
    {
        $min = new GeoCoordinates(-90.0, 0.0);
        $max = new GeoCoordinates(90.0, 0.0);

        $this->assertSame(-90.0, $min->getLatitude());
        $this->assertSame(90.0, $max->getLatitude());
    }

    public function testAcceptsBoundaryLongitude(): void
    {
        $min = new GeoCoordinates(0.0, -180.0);
        $max = new GeoCoordinates(0.0, 180.0);

        $this->assertSame(-180.0, $min->getLongitude());
        $this->assertSame(180.0, $max->getLongitude());
    }

    public function testRejectsLatitudeTooHigh(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GeoCoordinates(91.0, 0.0);
    }

    public function testRejectsLatitudeTooLow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GeoCoordinates(-91.0, 0.0);
    }

    public function testRejectsLongitudeTooHigh(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GeoCoordinates(0.0, 181.0);
    }

    public function testRejectsLongitudeTooLow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GeoCoordinates(0.0, -181.0);
    }
}
