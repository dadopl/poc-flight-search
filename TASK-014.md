# TASK-014 — Moduł cennika — CQRS i API cennika

**Tytuł biznesowy:** Udostępnienie API zarządzania cennikami i kalkulacji cen biletów

**Opis biznesowy:**
Administratorzy cennika muszą móc tworzyć i aktualizować cenniki dla lotów. Moduł wyszukiwania potrzebuje pobierać aktualną cenę dla znalezionych lotów aby wzbogacić wyniki. Endpoint kalkulatora ceny pozwala sprawdzić ile zapłacą pasażerowie przed zakupem — z pełnym wyjaśnieniem zastosowanych reguł cenowych. Jest to kluczowe dla transparentności cen.

**Wymagania:**
Komendy: `CreatePriceListCommand`, `UpdateBasePriceCommand`, `AddPricingRuleCommand`, `DeactivatePriceListCommand`. Zapytania: `GetCurrentPriceQuery` (FlightId + CabinClass + passengerCount + purchaseDate → PriceCalculationResult), `GetPriceListQuery`. Encja Doctrine z PricingRules serializowanymi jako JSON w kolumnie. Integracja z Search: `ExecuteSearchCommandHandler` wywołuje `GetCurrentPriceQuery` dla każdego lotu w wynikach. Endpointy: `POST /api/price-lists`, `GET /api/price-lists/{flightId}`, `GET /api/price-calculator?flightId=&cabin=&passengers=&purchaseDate=`.

**Definition of Ready:**
TASK-011 i TASK-013 ukończone. Zdecydowano że cennik jest per lot + klasa kabiny (nie per linia lotnicza). Potwierdzono że `GetCurrentPriceQuery` zwraca błąd gdy brak aktywnego cennika dla lotu. Zdecydowano format daty purchaseDate.

**Definition of Done:**
`GET /api/price-calculator` zwraca finalPrice i listę zastosowanych reguł z opisami. Wyniki `/api/search/{id}/results` zawierają wyliczoną cenę dla każdego lotu. Brak cennika dla lotu zwraca czytelny błąd 422. Testy integracyjne weryfikują że cena w wynikach wyszukiwania jest identyczna z kalkulatorem dla tych samych parametrów. PHPStan przechodzi.

