<?php

declare(strict_types=1);

namespace App\Tests\Search\Domain;

use App\Search\Domain\Exception\InvalidSearchCriteriaException;
use App\Search\Domain\ValueObject\CabinClass;
use App\Search\Domain\ValueObject\SearchCriteria;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SearchCriteriaTest extends TestCase
{
    private function tomorrow(): DateTimeImmutable
    {
        return new DateTimeImmutable('tomorrow');
    }

    private function makeCriteria(
        string $departure = 'WAW',
        string $arrival = 'KTW',
        ?DateTimeImmutable $departureDate = null,
        ?DateTimeImmutable $returnDate = null,
        int $passengerCount = 1,
        CabinClass $cabinClass = CabinClass::ECONOMY,
    ): SearchCriteria {
        return new SearchCriteria(
            $departure,
            $arrival,
            $departureDate ?? $this->tomorrow(),
            $returnDate,
            $passengerCount,
            $cabinClass,
        );
    }

    // --- departure date ---

    public function testValidCriteriaCreatesSuccessfully(): void
    {
        $criteria = $this->makeCriteria();

        $this->assertSame('WAW', $criteria->getDepartureIata());
        $this->assertSame('KTW', $criteria->getArrivalIata());
        $this->assertSame(1, $criteria->getPassengerCount());
        $this->assertSame(CabinClass::ECONOMY, $criteria->getCabinClass());
        $this->assertNull($criteria->getReturnDate());
    }

    public function testTodayDepartureDateIsValid(): void
    {
        $criteria = $this->makeCriteria(departureDate: new DateTimeImmutable('today'));

        $this->assertNotNull($criteria->getDepartureDate());
    }

    public function testYesterdayDepartureDateThrows(): void
    {
        $this->expectException(InvalidSearchCriteriaException::class);

        $this->makeCriteria(departureDate: new DateTimeImmutable('yesterday'));
    }

    public function testPastDepartureDateThrows(): void
    {
        $this->expectException(InvalidSearchCriteriaException::class);

        $this->makeCriteria(departureDate: new DateTimeImmutable('2020-01-01'));
    }

    // --- same departure and arrival ---

    public function testSameDepartureAndArrivalThrows(): void
    {
        $this->expectException(InvalidSearchCriteriaException::class);

        $this->makeCriteria(departure: 'KTW', arrival: 'KTW');
    }

    // --- passengerCount ---

    public function testPassengerCountOneIsValid(): void
    {
        $criteria = $this->makeCriteria(passengerCount: 1);

        $this->assertSame(1, $criteria->getPassengerCount());
    }

    public function testPassengerCountNineIsValid(): void
    {
        $criteria = $this->makeCriteria(passengerCount: 9);

        $this->assertSame(9, $criteria->getPassengerCount());
    }

    public function testPassengerCountZeroThrows(): void
    {
        $this->expectException(InvalidSearchCriteriaException::class);

        $this->makeCriteria(passengerCount: 0);
    }

    public function testPassengerCountTenThrows(): void
    {
        $this->expectException(InvalidSearchCriteriaException::class);

        $this->makeCriteria(passengerCount: 10);
    }

    public function testNegativePassengerCountThrows(): void
    {
        $this->expectException(InvalidSearchCriteriaException::class);

        $this->makeCriteria(passengerCount: -1);
    }

    // --- IATA validation ---

    public function testInvalidDepartureIataThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeCriteria(departure: 'INVALID');
    }

    public function testInvalidArrivalIataThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeCriteria(arrival: '12');
    }

    // --- return date ---

    public function testReturnDateIsOptional(): void
    {
        $returnDate = new DateTimeImmutable('next week');
        $criteria = $this->makeCriteria(returnDate: $returnDate);

        $this->assertEquals($returnDate, $criteria->getReturnDate());
    }

    // --- cabin class ---

    public function testBusinessCabinClass(): void
    {
        $criteria = $this->makeCriteria(cabinClass: CabinClass::BUSINESS);

        $this->assertSame(CabinClass::BUSINESS, $criteria->getCabinClass());
    }
}
