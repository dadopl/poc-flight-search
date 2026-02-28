# TASK-006 — Moduł lotów — bogaty agregat z polityką przejść statusów

**Tytuł biznesowy:** Zbudowanie modelu domenowego lotu z zarządzaniem statusem i cyklem życia

**Opis biznesowy:**
Lot to centralny byt systemu. Ma swój cykl życia — od zaplanowania, przez boarding, odlot, aż do lądowania lub anulowania. Nie wszystkie przejścia między statusami są dozwolone — anulowany lot nie może wzbić się w powietrze. Numer lotu musi spełniać format IATA (dwie litery linii lotniczej + 1-4 cyfry np. LO123, FR4567). Czas przylotu musi być późniejszy niż czas odlotu. Lotnisko odlotu i przylotu nie mogą być tym samym lotniskiem.

**Wymagania:**
Agregat `Flight` z value objectami: `FlightId`, `FlightNumber` (format IATA regex), `Aircraft` (model + totalSeats > 0). `FlightStatus` enum: SCHEDULED, BOARDING, DEPARTED, ARRIVED, CANCELLED, DELAYED. Invarianty w konstruktorze: departure != arrival, arrival > departure. Metody: `delay(newDeparture)`, `cancel(reason)`, `board()`, `depart()`, `arrive()` — każda rejestruje event. `FlightStatusTransitionPolicy` jako osobna klasa domenowa zwracająca dozwolone przejścia i wyrzucająca `InvalidFlightStatusTransitionException`. Port `FlightRepository` z findByRoute (przyjmuje dwa AirportId + DateTimeRange).

**Definition of Ready:**
TASK-004 ukończony. Ustalono pełną macierz dozwolonych przejść statusów. Potwierdzono format FlightNumber (regex). Zdecydowano że `reason` przy anulowaniu jest wymagany (niepusty string).

**Definition of Done:**
`cancel()` na locie ze statusem ARRIVED wyrzuca `InvalidFlightStatusTransitionException`. `FlightNumber` z "LOT123" (3 litery) wyrzuca wyjątek. Lot z tym samym lotniskiem odlotu i przylotu nie może być stworzony. Unit testy pokrywają całą macierz przejść statusów (każda niedozwolona kombinacja). Zero importów Symfony w `Flight/Domain`.

