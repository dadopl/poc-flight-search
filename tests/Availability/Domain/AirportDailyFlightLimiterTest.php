<?php

declare(strict_types=1);

namespace App\Tests\Availability\Domain;

use App\Availability\Domain\Repository\AirportDailyFlightLimitRepository;
use App\Availability\Domain\Service\AirportDailyFlightLimiter;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AirportDailyFlightLimiterTest extends TestCase
{
    private AirportDailyFlightLimitRepository&MockObject $limitRepository;
    private AirportDailyFlightLimiter $limiter;
    private DateTimeImmutable $date;

    protected function setUp(): void
    {
        $this->limitRepository = $this->createMock(AirportDailyFlightLimitRepository::class);
        $this->limiter = new AirportDailyFlightLimiter($this->limitRepository);
        $this->date = new DateTimeImmutable('2026-06-01');
    }

    public function testCanAcceptFlightWhenNoLimitConfigured(): void
    {
        $this->limitRepository
            ->method('findDailyLimitByIataCode')
            ->with('WAW')
            ->willReturn(null);

        $this->assertTrue($this->limiter->canAcceptFlight('WAW', $this->date, 100));
    }

    public function testCanAcceptFlightWhenBelowLimit(): void
    {
        $this->limitRepository
            ->method('findDailyLimitByIataCode')
            ->with('KTW')
            ->willReturn(2);

        $this->assertTrue($this->limiter->canAcceptFlight('KTW', $this->date, 1));
    }

    public function testCannotAcceptFlightWhenLimitReachedForKtw(): void
    {
        $this->limitRepository
            ->method('findDailyLimitByIataCode')
            ->with('KTW')
            ->willReturn(2);

        $this->assertFalse($this->limiter->canAcceptFlight('KTW', $this->date, 2));
    }

    public function testCannotAcceptFlightWhenLimitExceededForKtw(): void
    {
        $this->limitRepository
            ->method('findDailyLimitByIataCode')
            ->with('KTW')
            ->willReturn(2);

        $this->assertFalse($this->limiter->canAcceptFlight('KTW', $this->date, 3));
    }

    public function testCanAcceptFirstFlightForKtw(): void
    {
        $this->limitRepository
            ->method('findDailyLimitByIataCode')
            ->with('KTW')
            ->willReturn(2);

        $this->assertTrue($this->limiter->canAcceptFlight('KTW', $this->date, 0));
    }

    public function testCanAcceptFlightWhenLimitIsHighAndCountIsLow(): void
    {
        $this->limitRepository
            ->method('findDailyLimitByIataCode')
            ->with('WAW')
            ->willReturn(50);

        $this->assertTrue($this->limiter->canAcceptFlight('WAW', $this->date, 49));
    }

    public function testCannotAcceptFlightWhenLimitIsOneAndOneAlreadyScheduled(): void
    {
        $this->limitRepository
            ->method('findDailyLimitByIataCode')
            ->with('GDN')
            ->willReturn(1);

        $this->assertFalse($this->limiter->canAcceptFlight('GDN', $this->date, 1));
    }

    public function testDifferentDatesAreIndependent(): void
    {
        $this->limitRepository
            ->method('findDailyLimitByIataCode')
            ->with('KTW')
            ->willReturn(2);

        $date1 = new DateTimeImmutable('2026-06-01');
        $date2 = new DateTimeImmutable('2026-06-02');

        // On date1 the limit is reached (2 scheduled)
        $this->assertFalse($this->limiter->canAcceptFlight('KTW', $date1, 2));
        // On date2 only 1 is scheduled, so can accept
        $this->assertTrue($this->limiter->canAcceptFlight('KTW', $date2, 1));
    }
}
