# TASK-011 — Moduł wyszukiwania — orkiestracja i cache wyników

**Tytuł biznesowy:** Implementacja silnika wyszukiwania lotów łączącego dostępność, ceny i filtry

**Opis biznesowy:**
Serce systemu — serwis który przyjmuje kryteria wyszukiwania, pyta moduł dostępności o pasujące loty, wzbogaca wyniki o ceny z modułu pricing, aplikuje dodatkowe filtry użytkownika (max cena, czas trwania) i zwraca posortowaną listę. Wyniki są cache'owane na 5 minut aby wielokrotne odświeżanie wyników przez użytkownika nie generowało zbędnego obciążenia. Wyszukiwanie jest asynchroniczne — użytkownik dostaje sessionId i odpytuje o wyniki.

**Wymagania:**
`InitiateSearchCommand` / handler — tworzy SearchSession PENDING, dispatchuje `ExecuteSearchCommand`. `ExecuteSearchCommand` / handler — główna logika: wywołuje `CheckRouteAvailabilityQuery`, wzbogaca o ceny przez `GetCurrentPriceQuery`, aplikuje `SearchFilters`, sortuje (domyślnie cena rosnąco), zapisuje do `SearchResultsCache`, ustawia sesję COMPLETED. Port `SearchResultsCache` z adapterem `InMemorySearchResultsCache`. `GetSearchResultsQuery` zwracający wyniki z cache z paginacją. Port `SearchPort` jako jedyne wejście do modułu z zewnątrz.

**Definition of Ready:**
TASK-009 i TASK-010 ukończone. TASK-013 (Pricing) musi być ukończony równolegle lub wcześniej. Zdecydowano że sortowanie domyślne to cena rosnąco. Potwierdzono że InMemory cache wystarczy na POC.

**Definition of Done:**
Test integracyjny potwierdza pełny flow: InitiateSearch → ExecuteSearch → GetSearchResults. Wyniki zawierają wyliczoną cenę dla każdego lotu. Loty z KTW nie pojawiają się w wynikach gdy limiter jest aktywny. Filtr `maxPrice` poprawnie wyklucza droższe loty. Filtr `directOnly` działa (na POC wszystkie loty są bezpośrednie — filtr zawsze true ale musi być zaimplementowany). Paginacja działa poprawnie.

