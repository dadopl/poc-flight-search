# TASK-017 — Cache warstwy zapytań i optymalizacja wydajności

**Tytuł biznesowy:** Wdrożenie cache'owania wyników zapytań w celu poprawy wydajności wyszukiwania

**Opis biznesowy:**
Wyszukiwanie lotów to operacja kosztowna — odpytuje bazę, przelicza ceny przez polityki, filtruje przez limiter. Użytkownik często odświeża wyniki lub wielu użytkowników szuka na tej samej trasie. Cache zmniejsza obciążenie bazy i skraca czas odpowiedzi. Dane o lotniskach zmieniają się rzadko — mogą być cache'owane godzinę. Wyniki wyszukiwania są ważne 5 minut. Wyliczone ceny ważne 2 minuty (polityki zależą od czasu do odlotu który zmienia się co minutę).

**Wymagania:**
Symfony Cache z adapterem filesystem dla dev, zmienną `CACHE_DSN` dla prod (Redis-ready). `CachedAirportRepository` jako dekorator owijający Doctrine repozytorium — cache TTL 60 min dla `findAllActive()`, inwalidacja po eventach `AirportActivated`/`AirportDeactivated`. `CachedSearchResultsCache` implementacja portu `SearchResultsCache` — TTL 5 min. `CachedPriceCalculation` dekorator na `PriceCalculator` — klucz cache: flightId+cabinClass+passengerCount+date, TTL 2 min. Indeksy DB: flights po (departure_airport_id, arrival_airport_id, scheduled_departure), flight_availability po (flight_id, cabin_class), airports.iata_code unique.

**Definition of Ready:**
TASK-011 i TASK-014 ukończone. Zdecydowano wzorzec Decorator dla cache (nie modyfikujemy istniejących klas). Potwierdzono że Decorator jest wstrzykiwany przez DI zamiast oryginalnej klasy. Zaakceptowano że InMemory znika — zastępuje go CachedSearchResultsCache.

**Definition of Done:**
Drugie wywołanie `findAllActive()` nie generuje zapytania SQL (weryfikacja przez Symfony Profiler). Dezaktywacja lotniska inwaliduje cache — kolejne wywołanie pobiera z bazy. Czas odpowiedzi `GET /api/search/{id}/results` przy powtórnym zapytaniu < 50ms. Indeksy dodane w migracji i potwierdzone przez `EXPLAIN` na zapytaniach wyszukiwania.

