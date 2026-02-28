# TASK-010 — Moduł wyszukiwania — agregat sesji wyszukiwania

**Tytuł biznesowy:** Zbudowanie modelu sesji wyszukiwania z walidacją kryteriów i filtrowaniem wyników

**Opis biznesowy:**
Każde wyszukiwanie użytkownika to sesja która przechodzi przez stany — od oczekiwania, przez przetwarzanie, do zakończenia lub błędu. Sesja przechowuje kryteria wyszukiwania (skąd, dokąd, kiedy, ile osób, klasa kabiny) oraz opcjonalne filtry (maksymalna cena, tylko bezpośrednie). Kryteria muszą być walidowane: data nie może być w przeszłości, liczba pasażerów 1-9, lotnisko odlotu ≠ lotnisko przylotu.

**Wymagania:**
Agregat `SearchSession` z value objectami: `SearchSessionId`, `SearchCriteria` (departure IATA, arrival IATA, departureDate >= today, returnDate nullable, passengerCount 1-9, CabinClass), `SearchFilters` (maxPrice nullable Money, maxDurationMinutes nullable int, directOnly bool). `SearchStatus` enum: PENDING, PROCESSING, COMPLETED, FAILED. Metody agregatu: `start()`, `complete(int resultCount)`, `fail(string reason)`. `SearchResultItem` jako Value Object (nie encja): FlightId, FlightNumber, departure/arrival, czasy, dostępneMiejsca, cabinClass, basePrice Money. Port `SearchSessionRepository`.

**Definition of Ready:**
TASK-008 ukończony. Zdecydowano że SearchSession jest agregatem (nie DTO) aby móc rejestrować eventy. Potwierdzono że SearchResultItem to VO — nie jest persystowany w bazie. Zdecydowano że "today" sprawdzane jest w VO `SearchCriteria` przez `new DateTimeImmutable('today')`.

**Definition of Done:**
`SearchCriteria` z datą wczorajszą wyrzuca `InvalidSearchCriteriaException`. `SearchCriteria` z KTW jako departure i arrival wyrzuca wyjątek. `passengerCount` 0 lub 10 wyrzuca wyjątek. `complete()` na sesji w statusie FAILED wyrzuca wyjątek. Unit testy pokrywają wszystkie walidacje. Zero importów Symfony w `Search/Domain`.

