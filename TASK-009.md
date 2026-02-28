# TASK-009 — Moduł dostępności — CQRS i API sprawdzania dostępności

**Tytuł biznesowy:** Udostępnienie API do sprawdzania dostępnych lotów z uwzględnieniem limitów lotnisk

**Opis biznesowy:**
Główny endpoint wyszukiwania musi zwracać tylko loty które faktycznie mają wolne miejsca w żądanej klasie kabiny i spełniają limit dzienny lotniska. To jest kluczowy endpoint systemu — odpowiada bezpośrednio na pytanie "jakie loty są dostępne z A do B w dniu X dla N pasażerów". Jeśli lotnisko osiągnęło dzienny limit (jak KTW z limitem 2 lotów) — żadne loty z tego lotniska nie pojawią się w odpowiedzi tego dnia, niezależnie od faktycznej dostępności miejsc.

**Wymagania:**
Komendy: `InitializeFlightAvailabilityCommand`, `BlockSeatsCommand`, `ReleaseSeatsCommand`, `BookSeatsCommand`, `CancelBookingCommand`. Zapytanie `CheckRouteAvailabilityQuery` — przyjmuje departure IATA, arrival IATA, date, passengerCount, cabinClass — stosuje filtr `AirportDailyFlightLimiter` i filtr `availableSeats >= passengerCount` — zwraca tylko loty spełniające oba warunki. Endpointy: `GET /api/availability/check` (główny), `POST /api/flights/{flightId}/availability/initialize`, `GET /api/flights/{flightId}/availability`.

**Definition of Ready:**
TASK-007 i TASK-008 ukończone. Zdecydowano kolejność filtrów (najpierw limiter, potem dostępność). Potwierdzono że `InitializeFlightAvailability` jest wywoływane zaraz po stworzeniu lotu. Zdecydowano format query params dla głównego endpointu.

**Definition of Done:**
`GET /api/availability/check?from=KTW&to=WAW&date=X` zwraca pustą listę gdy KTW osiągnął limit 2 lotów. Ten sam endpoint zwraca wyniki gdy from=WAW (WAW ma limit 20). Lot z 0 dostępnymi miejscami nie pojawia się w wynikach. Testy funkcjonalne zawierają scenariusz "brak lotów z Katowic z powodu limitu". Seed danych testowych zawiera KTW z limitem 2 i co najmniej 2 zaplanowane loty z KTW w danym dniu.

