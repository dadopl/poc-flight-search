# TASK-008 — Moduł dostępności — agregat miejsc i limiter lotnisk

**Tytuł biznesowy:** Zbudowanie modelu dostępności miejsc z mechanizmem limitowania lotów per lotnisko

**Opis biznesowy:**
Każdy lot ma określoną liczbę miejsc w każdej klasie kabiny (economy, business, first). System musi śledzić ile miejsc zostało zarezerwowanych i ile jest jeszcze dostępnych. Kluczowa reguła biznesowa: lotnisko może być aktywnym punktem odlotu maksymalnie dla określonej liczby lotów danego dnia. Katowice (KTW) mają limit 2 lotów dziennie — jeśli limit jest osiągnięty, żadne loty z Katowic nie pojawią się w wynikach wyszukiwania na ten dzień. To jest główny mechanizm kontroli przepustowości lotnisk.

**Wymagania:**
Agregat `FlightAvailability` z polami: FlightId, `CabinClass` enum (ECONOMY, BUSINESS, FIRST), totalSeats, bookedSeats, blockedSeats, minimumAvailableThreshold. Metody: `book(int count)`, `cancelBooking(int count)`, `blockSeats(int count)`, `releaseBlockedSeats(int count)` — wszystkie sprawdzają invariant bookedSeats + blockedSeats <= totalSeats. Metody obliczeniowe: `availableSeats()`, `isAvailable()`, `isNearlyFull()`. `AirportDailyFlightLimiter` — serwis domenowy sprawdzający limit lotów per lotnisko per dzień. Port `AirportDailyFlightLimitRepository` przechowujący konfigurację limitów per lotnisko.

**Definition of Ready:**
TASK-006 ukończony. Zdecydowano że limit lotniska jest konfiguracją (nie zakodowaną stałą). Potwierdzono że `blockedSeats` to miejsca zarezerwowane dla specjalnych przypadków (np. crew). Zdecydowano że `minimumAvailableThreshold` jest per agregat (może być różny dla economy i business).

**Definition of Done:**
`book(5)` gdy dostępne są 3 miejsca wyrzuca `InsufficientSeatsException`. `blockSeats` i `book` razem nie mogą przekroczyć `totalSeats`. `AirportDailyFlightLimiter` zwraca false dla KTW gdy już 2 loty są zaplanowane na dany dzień. Unit testy pokrywają wszystkie edge case'y dostępności. Zero importów Symfony w `Availability/Domain`.

