# poc-flight-search

Flight Search Engine — proof-of-concept built with **Symfony 7.4**, **Domain-Driven Design**, **Hexagonal Architecture**, and **CQRS**.

The system demonstrates a real-world flight search platform with modular design, dynamic pricing policies, airport daily capacity limits, and asynchronous search via polling.

> **Note:** This repository is the source material for a documentation generator POC. Pull Requests follow a strict description template to produce high-quality architectural documentation.

## Architecture Overview

```mermaid
graph TD
    Client -->|HTTP| Search
    Search -->|CheckRouteAvailabilityQuery| Availability
    Search -->|GetCurrentPriceQuery| Pricing
    Availability -->|AirportDailyFlightLimiter| Airports
    Availability -->|FlightId| Flights
    Pricing -->|FlightId| Flights
    Airports -->|AirportId| SharedKernel
    Flights -->|AggregateRoot| SharedKernel
    Availability -->|AggregateRoot| SharedKernel
    Search -->|AggregateRoot| SharedKernel
    Pricing -->|Money VO| SharedKernel

    subgraph SharedKernel[Shared Kernel]
        AggregateRoot
        DomainEvent
        Money
        Uuid
        DateTimeRange
        CommandBus
        QueryBus
    end

    subgraph Airports[Airport Module]
        AirportAggregate[Airport Aggregate]
        IataCode[IataCode VO]
        GeoCoordinates
        AirportDailyFlightLimiter
    end

    subgraph Flights[Flight Module]
        FlightAggregate[Flight Aggregate]
        FlightStatus[FlightStatus Enum]
        FlightStatusTransitionPolicy
    end

    subgraph Availability[Availability Module]
        FlightAvailability[FlightAvailability Aggregate]
        CabinClass[CabinClass Enum]
    end

    subgraph Pricing[Pricing Module]
        PriceList[PriceList Aggregate]
        PriceCalculator
        EarlyBird[EarlyBirdPolicy -15%]
        LastMinute[LastMinutePolicy +30%]
        Occupancy[OccupancyPolicy +20%]
        GroupDiscount[PassengerCountPolicy -10%]
    end

    subgraph Search[Search Module]
        SearchSession[SearchSession Aggregate]
        SearchCriteria
        SearchResultsCache
    end
```

## Modules

### Shared Kernel
Common domain building blocks used across all modules. **No Symfony imports allowed.**

| Component | Description |
|-----------|-------------|
| `AggregateRoot` | Base class with domain event registration and `pullEvents()` |
| `DomainEvent` | Base event with UUID, timestamp, aggregate ID |
| `Money` | Amount in grosz (int) + currency (PLN/EUR/USD). Supports add/subtract/compare |
| `Uuid` | Type-safe UUID v4 wrapper |
| `DateTimeRange` | Date range with `from < to` validation |
| `Pagination` | Page + per-page value object |
| `CommandBus` / `QueryBus` | Ports for CQRS messaging |

### Airport Module
Manages airports as active departure/arrival points. Enforces IATA code format and geographic coordinates validation.

**Key rules:**
- `IataCode` must be exactly 3 uppercase letters (e.g. `WAW`, `KTW`, `KRK`)
- `Country` follows ISO 3166-1 alpha-2
- Deactivated airports do not appear in search results
- `activate()` / `deactivate()` register domain events

**Daily flight limiter:** Each airport has a configurable daily flight limit. KTW is intentionally set to **2 flights/day** to demonstrate the capacity control mechanism — if the limit is reached, no flights from that airport appear in availability results for that day.

**Endpoints:**
```
POST   /api/airports
GET    /api/airports
GET    /api/airports/{iataCode}
```

### Flight Module
Central entity of the system. Manages the full flight lifecycle with a strict status transition policy.

**Status machine:**
```
SCHEDULED → BOARDING → DEPARTED → ARRIVED
SCHEDULED → DELAYED → BOARDING → ...
SCHEDULED → CANCELLED
BOARDING  → CANCELLED
DELAYED   → CANCELLED
```
Any other transition throws `InvalidFlightStatusTransitionException` (HTTP 409).

**Key rules:**
- `FlightNumber` must match IATA format: 2 letters + 1-4 digits (e.g. `LO123`, `FR4567`)
- Departure airport ≠ arrival airport
- Arrival time must be later than departure time
- `cancel(reason)` requires a non-empty reason string

**Endpoints:**
```
POST   /api/flights
GET    /api/flights
GET    /api/flights/{flightNumber}
PATCH  /api/flights/{flightNumber}/status
```

### Availability Module
Tracks seat availability per flight per cabin class. Enforces the airport daily flight limit as the primary search filter.

**Cabin classes:** `ECONOMY`, `BUSINESS`, `FIRST`

**Seat invariant:** `bookedSeats + blockedSeats ≤ totalSeats` — violation throws `InsufficientSeatsException`.

**Operations:**
- `book(count)` — reserve seats for passengers
- `cancelBooking(count)` — release reserved seats
- `blockSeats(count)` — hold seats (e.g. for crew)
- `releaseBlockedSeats(count)` — unblock held seats
- `isNearlyFull()` — true when < 20% seats available (triggers `OccupancyBasedPricingPolicy`)

**Main search filter applied in order:**
1. `AirportDailyFlightLimiter` — excludes all flights from airports that reached daily limit
2. `availableSeats >= passengerCount` — excludes full flights

**Endpoints:**
```
GET    /api/availability/check?from=WAW&to=CDG&date=2025-06-01&passengers=2&cabin=ECONOMY
POST   /api/flights/{flightId}/availability/initialize
GET    /api/flights/{flightId}/availability
```

### Search Module
Orchestrates the search flow. Uses an asynchronous polling pattern — the client receives a `sessionId` immediately and polls for results.

**Flow:**
```
POST /api/search
  → creates SearchSession (PENDING)
  → dispatches ExecuteSearchCommand
    → CheckRouteAvailabilityQuery (Availability module)
    → GetCurrentPriceQuery per flight (Pricing module)
    → applies SearchFilters (maxPrice, maxDuration, directOnly)
    → sorts by price ascending (default)
    → stores results in SearchResultsCache (TTL 5 min)
    → sets session COMPLETED
  ← returns 202 with sessionId

GET /api/search/{sessionId}/results
  ← 200 + results when COMPLETED
  ← 202 when still PROCESSING
  ← 422 + reason when FAILED
```

**Search criteria validation:**
- `departureDate` must not be in the past
- `passengerCount` between 1 and 9
- departure airport ≠ arrival airport

**Rate limiting:** 100 requests/minute per IP.

**Endpoints:**
```
POST   /api/search
GET    /api/search/{sessionId}/results
```

### Pricing Module
Calculates ticket prices using stackable pricing policies applied sequentially to the base price.

| Policy | Condition | Effect |
|--------|-----------|--------|
| `EarlyBirdPricingPolicy` | Purchase > 30 days before departure | -15% |
| `LastMinutePricingPolicy` | Purchase < 7 days before departure | +30% |
| `OccupancyBasedPricingPolicy` | < 20% seats available | +20% |
| `PassengerCountPricingPolicy` | ≥ 5 passengers | -10% |

Policies are additive on percentages. Price cannot drop below 0. `PriceCalculationResult` includes `finalPrice` and a list of `appliedRules` with human-readable descriptions for transparency.

**Endpoints:**
```
POST   /api/price-lists
GET    /api/price-lists/{flightId}
GET    /api/price-calculator?flightId=&cabin=&passengers=&purchaseDate=
```

## Requirements

- PHP 8.4 (hard requirement — enforced by `composer.json`)
- Composer
- SQLite extension (development)
- PostgreSQL 16+ (production)

## Quick Start (Local)

```bash
# 1. Install dependencies
composer install

# 2. Copy and configure environment
cp .env .env.local
# Edit .env.local — set APP_SECRET at minimum

# 3. Run database migrations
make migrate

# 4. Load fixtures (test data)
make fixtures

# 5. Verify setup
php bin/console about
php bin/console server:start
```

## Quick Start (Docker)

```bash
# Build and start containers
docker compose up -d

# Run migrations inside the container
docker compose exec app make migrate

# Load fixtures
docker compose exec app make fixtures
```

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_ENV` | `dev` | Application environment (`dev`, `prod`, `test`) |
| `APP_SECRET` | — | **Required.** Symfony secret key |
| `DATABASE_URL` | `sqlite:///%kernel.project_dir%/var/data.db` | Database connection string |
| `CACHE_DSN` | `filesystem` adapter | Cache backend (Redis URL for production) |

**Development (SQLite — no server needed):**
```
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

**Production (PostgreSQL):**
```
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/flight_search?serverVersion=16&charset=utf8"
```

**Tests** use a separate SQLite database automatically (`var/data_test.db` via `.env.test`).

## Development Commands

```bash
make test       # Run PHPUnit test suite
make stan       # PHPStan level 8 static analysis
make cs         # PHP_CodeSniffer PSR-12 check
make migrate    # Run Doctrine migrations
make fixtures   # Load test data (idempotent)
```

## Test Data (Fixtures)

Fixtures include airports, flights, availability and price lists designed to demonstrate all system features:

| Airport | Daily Limit | Notes |
|---------|-------------|-------|
| KTW | **2** | Intentionally low — limiter demo |
| WAW | 20 | Main hub |
| KRK, GDN, WRO | 10 | Regional |
| CDG, LHR, FRA | 10 | International destinations |

The fixtures load **3 flights from KTW** scheduled for tomorrow, exceeding the limit of 2. This means:

```bash
# Returns empty list — KTW daily limit reached
curl "http://localhost:8000/api/availability/check?from=KTW&to=WAW&date=$(date -d tomorrow +%Y-%m-%d)&passengers=1&cabin=ECONOMY"

# Returns results — WAW has limit of 20
curl "http://localhost:8000/api/availability/check?from=WAW&to=CDG&date=$(date -d tomorrow +%Y-%m-%d)&passengers=1&cabin=ECONOMY"
```

## API Documentation

Swagger UI with full OpenAPI 3.0 documentation is available at:
```
http://localhost:8000/api/doc
```

All endpoints, request/response schemas and examples are documented via `#[OA\...]` attributes on controllers and DTOs.

## HTTP Response Format

**Success:**
```json
{
  "data": { ... },
  "meta": {
    "correlationId": "uuid-v4",
    "timestamp": "2025-06-01T12:00:00+00:00"
  }
}
```

**Error:**
```json
{
  "error": {
    "code": "AIRPORT_NOT_FOUND",
    "message": "Airport with IATA code XYZ not found",
    "details": {}
  }
}
```

Every response includes an `X-Correlation-Id` header. Pass your own `X-Correlation-Id` in the request to trace it through logs.

**HTTP status codes:**
| Code | Meaning |
|------|---------|
| 200 | Success |
| 202 | Accepted (search in progress) |
| 400 | Bad request (`InvalidArgumentException`) |
| 404 | Not found (`NotFoundException`) |
| 409 | Conflict (invalid status transition) |
| 422 | Validation / domain rule violation (`DomainException`) |
| 429 | Rate limit exceeded |
| 500 | Unexpected server error (stack trace hidden in `prod`) |

## CI/CD

GitHub Actions runs on every push and pull request:

```
checkout → PHP 8.4 setup → composer install → make stan → make cs → make test → upload coverage
```

Pull Requests require:
- Description ≥ 200 characters
- Sections: *What was done*, *Architectural decisions*, *Rejected alternatives*, *Impact on other modules*, *How to test*, *Known limitations*
- Module label: `module:airport`, `module:flight`, `module:search`, `module:pricing`, `module:availability`, `module:shared`

See `.github/workflows/ci.yml` and `.github/pull_request_template.md`.

## Further Documentation

- [`docs/architecture-decisions.md`](docs/architecture-decisions.md) — why hexagonal architecture, CQRS, polling, `AirportDailyFlightLimiter` as a domain service
- [`docs/known-limitations.md`](docs/known-limitations.md) — no authentication, synchronous event publisher, KTW limiter details with code walkthrough