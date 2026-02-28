# TASK-007 — Moduł lotów — API zarządzania lotami

**Tytuł biznesowy:** Udostępnienie API do planowania lotów i zarządzania ich statusem

**Opis biznesowy:**
Dyspozytorzy linii lotniczych muszą móc planować nowe loty, aktualizować ich status (opóźnienie, anulowanie, boarding) oraz przeszukiwać loty po trasie i dacie. System wyszukiwania będzie korzystał z repozytorium lotów aby znaleźć loty pasujące do kryteriów użytkownika. Błędy biznesowe (niedozwolona zmiana statusu, niepoprawny format numeru) muszą zwracać czytelne komunikaty a nie błędy 500.

**Wymagania:**
Komendy: `ScheduleFlightCommand`, `DelayFlightCommand`, `CancelFlightCommand`, `BoardFlightCommand`. Zapytania: `GetFlightQuery`, `ListFlightsByRouteQuery` (departure IATA + arrival IATA + date), `ListFlightsByStatusQuery`. Encja Doctrine z mapperem — status jako string w bazie. Endpointy: `POST /api/flights`, `GET /api/flights/{flightNumber}`, `GET /api/flights` (query params: from, to, date, status), `PATCH /api/flights/{flightNumber}/status`. `ExceptionListener` rozszerzony o `InvalidFlightStatusTransitionException` → 409 Conflict.

**Definition of Ready:**
TASK-005 i TASK-006 ukończone. Zdecydowano format daty w query params (Y-m-d). Potwierdzono że `PATCH /status` przyjmuje nowy status i opcjonalny reason. Zaakceptowano 409 jako kod dla konfliktu statusu.

**Definition of Done:**
`PATCH` z niedozwoloną zmianą statusu zwraca 409 z opisem dozwolonych przejść. `GET /api/flights` z nieistniejącym kodem IATA zwraca 422. `GET /api/flights` bez parametrów zwraca wszystkie loty z paginacją. Testy funkcjonalne pokrywają pełny cykl życia lotu przez HTTP. Encja Doctrine nie ma żadnej logiki biznesowej.

