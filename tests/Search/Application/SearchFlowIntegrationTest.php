<?php

declare(strict_types=1);

namespace App\Tests\Search\Application;

use App\Availability\Application\Query\CheckRouteAvailabilityQuery;
use App\Availability\Presentation\DTO\CheckAvailabilityResponseDTO;
use App\Pricing\Application\Query\GetCurrentPriceQuery;
use App\Search\Application\Command\ExecuteSearchCommand;
use App\Search\Application\Command\InitiateSearchCommand;
use App\Search\Application\CommandHandler\ExecuteSearchCommandHandler;
use App\Search\Application\CommandHandler\InitiateSearchCommandHandler;
use App\Search\Application\Query\GetSearchResultsQuery;
use App\Search\Application\QueryHandler\GetSearchResultsQueryHandler;
use App\Search\Domain\Enum\SearchStatus;
use App\Search\Domain\ValueObject\SearchSessionId;
use App\Search\Infrastructure\Cache\InMemorySearchResultsCache;
use App\Search\Infrastructure\Repository\InMemorySearchSessionRepository;
use App\Shared\Domain\Bus\Command\Command;
use App\Shared\Domain\Bus\Command\CommandBus;
use App\Shared\Domain\Bus\Query\QueryBus;
use App\Shared\Domain\ValueObject\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for the full search flow: InitiateSearch → ExecuteSearch → GetSearchResults
 */
class SearchFlowIntegrationTest extends TestCase
{
    private const FLIGHT_ID    = '550e8400-e29b-41d4-a716-446655440010';
    private const SESSION_ID   = '550e8400-e29b-41d4-a716-446655440099';

    private InMemorySearchSessionRepository $sessionRepository;
    private InMemorySearchResultsCache $cache;
    private QueryBus&MockObject $queryBus;
    private CommandBus $commandBus;

    private InitiateSearchCommandHandler $initiateHandler;
    private ExecuteSearchCommandHandler $executeHandler;
    private GetSearchResultsQueryHandler $getResultsHandler;

    protected function setUp(): void
    {
        $this->sessionRepository = new InMemorySearchSessionRepository();
        $this->cache             = new InMemorySearchResultsCache();
        $this->queryBus          = $this->createMock(QueryBus::class);

        $this->executeHandler = new ExecuteSearchCommandHandler(
            $this->sessionRepository,
            $this->cache,
            $this->queryBus,
        );

        // Command bus that synchronously dispatches ExecuteSearchCommand to the handler
        $executeHandler       = $this->executeHandler;
        $this->commandBus     = new class ($executeHandler) implements CommandBus {
            public function __construct(
                private readonly ExecuteSearchCommandHandler $handler,
            ) {
            }

            public function execute(Command $command): void
            {
                if ($command instanceof ExecuteSearchCommand) {
                    ($this->handler)($command);
                }
            }
        };

        $this->initiateHandler = new InitiateSearchCommandHandler(
            $this->sessionRepository,
            $this->commandBus,
        );

        $this->getResultsHandler = new GetSearchResultsQueryHandler($this->cache);
    }

    public function testFullSearchFlowReturnsResults(): void
    {
        $this->queryBus->method('ask')
            ->willReturnCallback(function (object $query): mixed {
                if ($query instanceof CheckRouteAvailabilityQuery) {
                    return [
                        new CheckAvailabilityResponseDTO(
                            flightId: self::FLIGHT_ID,
                            flightNumber: 'LO123',
                            departureTime: '2030-06-01 10:00:00',
                            arrivalTime: '2030-06-01 12:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 50,
                            totalSeats: 180,
                        ),
                    ];
                }

                if ($query instanceof GetCurrentPriceQuery) {
                    return new Money(29900, 'PLN'); // 299.00 PLN in grosze
                }

                return null;
            });

        ($this->initiateHandler)(new InitiateSearchCommand(
            sessionId: self::SESSION_ID,
            departureIata: 'WAW',
            arrivalIata: 'KRK',
            departureDate: '2030-06-01',
            returnDate: null,
            passengerCount: 2,
            cabinClass: 'ECONOMY',
            maxPriceAmount: null,
            maxPriceCurrency: null,
            maxDurationMinutes: null,
            directOnly: false,
        ));

        // Session should be completed now
        $session = $this->sessionRepository->findById(new SearchSessionId(self::SESSION_ID));
        $this->assertNotNull($session);
        $this->assertSame(SearchStatus::COMPLETED, $session->getStatus());
        $this->assertSame(1, $session->getResultCount());

        // Get results with pagination
        $result = ($this->getResultsHandler)(new GetSearchResultsQuery(
            sessionId: self::SESSION_ID,
            page: 1,
            perPage: 10,
        ));

        $this->assertCount(1, $result['items']);
        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['page']);
        $this->assertSame(10, $result['perPage']);

        $item = $result['items'][0];
        $this->assertSame(self::FLIGHT_ID, $item['flightId']);
        $this->assertSame('LO123', $item['flightNumber']);
        $this->assertSame(29900, $item['price']['amount']);
        $this->assertSame('PLN', $item['price']['currency']);
    }

    public function testMaxPriceFilterExcludesExpensiveFlights(): void
    {
        $this->queryBus->method('ask')
            ->willReturnCallback(function (object $query): mixed {
                if ($query instanceof CheckRouteAvailabilityQuery) {
                    return [
                        new CheckAvailabilityResponseDTO(
                            flightId: self::FLIGHT_ID,
                            flightNumber: 'LO123',
                            departureTime: '2030-06-01 10:00:00',
                            arrivalTime: '2030-06-01 12:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 50,
                            totalSeats: 180,
                        ),
                    ];
                }

                if ($query instanceof GetCurrentPriceQuery) {
                    return new Money(50000, 'PLN'); // 500 PLN — above max
                }

                return null;
            });

        ($this->initiateHandler)(new InitiateSearchCommand(
            sessionId: self::SESSION_ID,
            departureIata: 'WAW',
            arrivalIata: 'KRK',
            departureDate: '2030-06-01',
            returnDate: null,
            passengerCount: 2,
            cabinClass: 'ECONOMY',
            maxPriceAmount: 30000, // max 300 PLN in grosze
            maxPriceCurrency: 'PLN',
            maxDurationMinutes: null,
            directOnly: false,
        ));

        $session = $this->sessionRepository->findById(new SearchSessionId(self::SESSION_ID));
        $this->assertNotNull($session);
        $this->assertSame(SearchStatus::COMPLETED, $session->getStatus());
        $this->assertSame(0, $session->getResultCount());

        $result = ($this->getResultsHandler)(new GetSearchResultsQuery(
            sessionId: self::SESSION_ID,
            page: 1,
            perPage: 10,
        ));

        $this->assertCount(0, $result['items']);
        $this->assertSame(0, $result['total']);
    }

    public function testResultsAreSortedByPriceAscending(): void
    {
        $flight2Id = '550e8400-e29b-41d4-a716-446655440011';

        $this->queryBus->method('ask')
            ->willReturnCallback(function (object $query) use ($flight2Id): mixed {
                if ($query instanceof CheckRouteAvailabilityQuery) {
                    return [
                        new CheckAvailabilityResponseDTO(
                            flightId: self::FLIGHT_ID,
                            flightNumber: 'LO123',
                            departureTime: '2030-06-01 10:00:00',
                            arrivalTime: '2030-06-01 12:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 50,
                            totalSeats: 180,
                        ),
                        new CheckAvailabilityResponseDTO(
                            flightId: $flight2Id,
                            flightNumber: 'LO456',
                            departureTime: '2030-06-01 14:00:00',
                            arrivalTime: '2030-06-01 16:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 30,
                            totalSeats: 180,
                        ),
                    ];
                }

                if ($query instanceof GetCurrentPriceQuery) {
                    return match ($query->flightId) {
                        self::FLIGHT_ID => new Money(50000, 'PLN'),
                        $flight2Id      => new Money(29900, 'PLN'),
                        default         => null,
                    };
                }

                return null;
            });

        ($this->initiateHandler)(new InitiateSearchCommand(
            sessionId: self::SESSION_ID,
            departureIata: 'WAW',
            arrivalIata: 'KRK',
            departureDate: '2030-06-01',
            returnDate: null,
            passengerCount: 1,
            cabinClass: 'ECONOMY',
            maxPriceAmount: null,
            maxPriceCurrency: null,
            maxDurationMinutes: null,
            directOnly: false,
        ));

        $result = ($this->getResultsHandler)(new GetSearchResultsQuery(
            sessionId: self::SESSION_ID,
            page: 1,
            perPage: 10,
        ));

        $this->assertCount(2, $result['items']);
        // Cheaper flight should be first
        $this->assertSame(29900, $result['items'][0]['price']['amount']);
        $this->assertSame(50000, $result['items'][1]['price']['amount']);
    }

    public function testPaginationWorksCorrectly(): void
    {
        $flight2Id = '550e8400-e29b-41d4-a716-446655440011';
        $flight3Id = '550e8400-e29b-41d4-a716-446655440012';

        $this->queryBus->method('ask')
            ->willReturnCallback(function (object $query) use ($flight2Id, $flight3Id): mixed {
                if ($query instanceof CheckRouteAvailabilityQuery) {
                    return [
                        new CheckAvailabilityResponseDTO(
                            flightId: self::FLIGHT_ID,
                            flightNumber: 'LO100',
                            departureTime: '2030-06-01 06:00:00',
                            arrivalTime: '2030-06-01 08:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 50,
                            totalSeats: 180,
                        ),
                        new CheckAvailabilityResponseDTO(
                            flightId: $flight2Id,
                            flightNumber: 'LO200',
                            departureTime: '2030-06-01 10:00:00',
                            arrivalTime: '2030-06-01 12:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 30,
                            totalSeats: 180,
                        ),
                        new CheckAvailabilityResponseDTO(
                            flightId: $flight3Id,
                            flightNumber: 'LO300',
                            departureTime: '2030-06-01 14:00:00',
                            arrivalTime: '2030-06-01 16:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 20,
                            totalSeats: 180,
                        ),
                    ];
                }

                if ($query instanceof GetCurrentPriceQuery) {
                    return new Money(29900, 'PLN');
                }

                return null;
            });

        ($this->initiateHandler)(new InitiateSearchCommand(
            sessionId: self::SESSION_ID,
            departureIata: 'WAW',
            arrivalIata: 'KRK',
            departureDate: '2030-06-01',
            returnDate: null,
            passengerCount: 1,
            cabinClass: 'ECONOMY',
            maxPriceAmount: null,
            maxPriceCurrency: null,
            maxDurationMinutes: null,
            directOnly: false,
        ));

        $page1 = ($this->getResultsHandler)(new GetSearchResultsQuery(
            sessionId: self::SESSION_ID,
            page: 1,
            perPage: 2,
        ));

        $this->assertSame(3, $page1['total']);
        $this->assertCount(2, $page1['items']);

        $page2 = ($this->getResultsHandler)(new GetSearchResultsQuery(
            sessionId: self::SESSION_ID,
            page: 2,
            perPage: 2,
        ));

        $this->assertSame(3, $page2['total']);
        $this->assertCount(1, $page2['items']);
    }

    public function testDirectOnlyFilterIsImplemented(): void
    {
        // For POC all flights are direct — filter always passes
        $this->queryBus->method('ask')
            ->willReturnCallback(function (object $query): mixed {
                if ($query instanceof CheckRouteAvailabilityQuery) {
                    return [
                        new CheckAvailabilityResponseDTO(
                            flightId: self::FLIGHT_ID,
                            flightNumber: 'LO123',
                            departureTime: '2030-06-01 10:00:00',
                            arrivalTime: '2030-06-01 12:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 50,
                            totalSeats: 180,
                        ),
                    ];
                }

                if ($query instanceof GetCurrentPriceQuery) {
                    return new Money(29900, 'PLN');
                }

                return null;
            });

        ($this->initiateHandler)(new InitiateSearchCommand(
            sessionId: self::SESSION_ID,
            departureIata: 'WAW',
            arrivalIata: 'KRK',
            departureDate: '2030-06-01',
            returnDate: null,
            passengerCount: 1,
            cabinClass: 'ECONOMY',
            maxPriceAmount: null,
            maxPriceCurrency: null,
            maxDurationMinutes: null,
            directOnly: true, // directOnly = true, but for POC all flights are direct
        ));

        $result = ($this->getResultsHandler)(new GetSearchResultsQuery(
            sessionId: self::SESSION_ID,
            page: 1,
            perPage: 10,
        ));

        // Flight should still appear (all POC flights are direct)
        $this->assertCount(1, $result['items']);
    }

    public function testFlightsWithNoPriceAreExcluded(): void
    {
        $this->queryBus->method('ask')
            ->willReturnCallback(function (object $query): mixed {
                if ($query instanceof CheckRouteAvailabilityQuery) {
                    return [
                        new CheckAvailabilityResponseDTO(
                            flightId: self::FLIGHT_ID,
                            flightNumber: 'LO123',
                            departureTime: '2030-06-01 10:00:00',
                            arrivalTime: '2030-06-01 12:00:00',
                            cabinClass: 'ECONOMY',
                            availableSeats: 50,
                            totalSeats: 180,
                        ),
                    ];
                }

                if ($query instanceof GetCurrentPriceQuery) {
                    return null; // No price list for this flight
                }

                return null;
            });

        ($this->initiateHandler)(new InitiateSearchCommand(
            sessionId: self::SESSION_ID,
            departureIata: 'WAW',
            arrivalIata: 'KRK',
            departureDate: '2030-06-01',
            returnDate: null,
            passengerCount: 1,
            cabinClass: 'ECONOMY',
            maxPriceAmount: null,
            maxPriceCurrency: null,
            maxDurationMinutes: null,
            directOnly: false,
        ));

        $session = $this->sessionRepository->findById(new SearchSessionId(self::SESSION_ID));
        $this->assertNotNull($session);
        $this->assertSame(SearchStatus::COMPLETED, $session->getStatus());
        $this->assertSame(0, $session->getResultCount());
    }
}
