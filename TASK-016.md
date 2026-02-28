# TASK-016 — Migracje bazy danych i dane testowe

**Tytuł biznesowy:** Przygotowanie struktury bazy danych i realistycznych danych testowych

**Opis biznesowy:**
System potrzebuje danych testowych które pozwolą na demonstrację wszystkich funkcjonalności — w szczególności scenariusza braku lotów z Katowic z powodu limitu przepustowości lotniska. Dane muszą być realistyczne i pokrywać różne przypadki: loty dostępne, loty pełne, loty z Katowic osiągające limit, różne klasy cenowe. Migracje muszą być deterministyczne i działać zarówno na SQLite (dev) jak i PostgreSQL (prod).

**Wymagania:**
Migracje Doctrine dla wszystkich tabel z `created_at` i `updated_at` przez `TimestampableTrait`. Command `app:fixtures:load`. Lotniska: WAW, KTW, KRK, GDN, WRO, CDG, LHR, FRA. Limity dzienne: KTW=2 (celowo niski), WAW=20, pozostałe=10. Minimum 3 loty z KTW zaplanowane na "jutro" (limiter musi je blokować). Minimum 10 lotów z WAW na różne destynacje. Loty w różnych statusach. Dostępność: kilka lotów prawie pełnych (<20% miejsc — OccupancyBasedPricingPolicy powinna się aktywować). Cenniki dla wszystkich lotów z różnymi cenami bazowymi.

**Definition of Ready:**
Wszystkie poprzednie taski (001-015) ukończone. Zdecydowano które daty używać w fixtures (relative: 'tomorrow', '+7 days'). Potwierdzono że fixtures są idempotentne (można uruchomić wielokrotnie). Zdecydowano że fixtures ładują się przez `make fixtures`.

**Definition of Done:**
`make fixtures` działa bez błędów. `GET /api/availability/check?from=KTW&to=WAW&date=[jutro]` zwraca pustą listę. `GET /api/availability/check?from=WAW&to=CDG&date=[jutro]` zwraca wyniki. `GET /api/price-calculator` dla lotu z <20% dostępnością zwraca dopłatę OccupancyBased. Baza po fixtures zawiera dokładnie tyle rekordów ile zdefiniowano w fixtures. `make migrate && make fixtures` działa od zera.

