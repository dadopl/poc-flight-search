<?php

declare(strict_types=1);

namespace App\Tests\Search\Domain;

use App\Search\Domain\Aggregate\SearchSession;
use App\Search\Domain\Event\SearchSessionCompleted;
use App\Search\Domain\Event\SearchSessionFailed;
use App\Search\Domain\Event\SearchSessionStarted;
use App\Search\Domain\Exception\InvalidSearchSessionStateException;
use App\Search\Domain\ValueObject\CabinClass;
use App\Search\Domain\ValueObject\SearchCriteria;
use App\Search\Domain\ValueObject\SearchFilters;
use App\Search\Domain\ValueObject\SearchSessionId;
use App\Search\Domain\ValueObject\SearchStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SearchSessionTest extends TestCase
{
    private function makeSession(): SearchSession
    {
        $criteria = new SearchCriteria(
            'WAW',
            'KTW',
            new DateTimeImmutable('tomorrow'),
            null,
            2,
            CabinClass::ECONOMY,
        );

        $filters = new SearchFilters(null, null, false);

        return SearchSession::create(
            SearchSessionId::generate(),
            $criteria,
            $filters,
        );
    }

    // --- initial state ---

    public function testNewSessionHasPendingStatus(): void
    {
        $session = $this->makeSession();

        $this->assertSame(SearchStatus::PENDING, $session->getStatus());
    }

    public function testNewSessionHasNoResultCount(): void
    {
        $session = $this->makeSession();

        $this->assertNull($session->getResultCount());
    }

    public function testNewSessionHasNoFailureReason(): void
    {
        $session = $this->makeSession();

        $this->assertNull($session->getFailureReason());
    }

    // --- start() ---

    public function testStartTransitionsToProcessing(): void
    {
        $session = $this->makeSession();
        $session->start();

        $this->assertSame(SearchStatus::PROCESSING, $session->getStatus());
    }

    public function testStartRecordsSearchSessionStartedEvent(): void
    {
        $session = $this->makeSession();
        $session->start();

        $events = $session->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(SearchSessionStarted::class, $events[0]);
    }

    public function testStartFromProcessingThrows(): void
    {
        $session = $this->makeSession();
        $session->start();

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->start();
    }

    public function testStartFromCompletedThrows(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->complete(5);

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->start();
    }

    public function testStartFromFailedThrows(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->fail('Error');

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->start();
    }

    // --- complete() ---

    public function testCompleteTransitionsToCompleted(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->complete(10);

        $this->assertSame(SearchStatus::COMPLETED, $session->getStatus());
        $this->assertSame(10, $session->getResultCount());
    }

    public function testCompleteRecordsSearchSessionCompletedEvent(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->pullEvents();
        $session->complete(10);

        $events = $session->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(SearchSessionCompleted::class, $events[0]);
        $this->assertSame(10, $events[0]->getResultCount());
    }

    public function testCompleteWithZeroResultsIsValid(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->complete(0);

        $this->assertSame(SearchStatus::COMPLETED, $session->getStatus());
        $this->assertSame(0, $session->getResultCount());
    }

    public function testCompleteFromPendingThrows(): void
    {
        $session = $this->makeSession();

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->complete(5);
    }

    public function testCompleteFromFailedThrows(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->fail('Error');

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->complete(5);
    }

    public function testCompleteFromCompletedThrows(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->complete(5);

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->complete(10);
    }

    // --- fail() ---

    public function testFailTransitionsToFailed(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->fail('Connection timeout');

        $this->assertSame(SearchStatus::FAILED, $session->getStatus());
        $this->assertSame('Connection timeout', $session->getFailureReason());
    }

    public function testFailRecordsSearchSessionFailedEvent(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->pullEvents();
        $session->fail('Timeout');

        $events = $session->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(SearchSessionFailed::class, $events[0]);
        $this->assertSame('Timeout', $events[0]->getReason());
    }

    public function testFailFromPendingThrows(): void
    {
        $session = $this->makeSession();

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->fail('Error');
    }

    public function testFailFromCompletedThrows(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->complete(5);

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->fail('Error');
    }

    public function testFailFromFailedThrows(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->fail('First error');

        $this->expectException(InvalidSearchSessionStateException::class);
        $session->fail('Second error');
    }

    // --- pullEvents() ---

    public function testPullEventsClearsEventList(): void
    {
        $session = $this->makeSession();
        $session->start();
        $session->pullEvents();

        $this->assertEmpty($session->pullEvents());
    }
}
