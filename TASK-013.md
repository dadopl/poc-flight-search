# TASK-013 — Moduł cennika — agregat i polityki cenowe

**Tytuł biznesowy:** Zbudowanie modelu cennika z dynamicznymi politykami cenowymi

**Opis biznesowy:**
Cena biletu nie jest stała — zmienia się w zależności od tego kiedy kupujemy (early bird taniej, last minute drożej), jak bardzo lot jest zapełniony (mało miejsc = wyższa cena) oraz od liczby pasażerów (zniżka grupowa). Każdy lot i klasa kabiny mają osobny cennik z ceną bazową i listą reguł cenowych. Kalkulator ceny stosuje reguły po kolei i zwraca finalną cenę wraz z listą zastosowanych modyfikacji (transparentność dla użytkownika).

**Wymagania:**
Agregat `PriceList` z polami: FlightId, CabinClass, basePrice Money, kolekcja `PricingRule` VO, validFrom, validTo, isActive. Polityki domenowe implementujące port `PricingPolicy`: `EarlyBirdPricingPolicy` (> 30 dni przed wylotem → -15%), `LastMinutePricingPolicy` (< 7 dni → +30%), `OccupancyBasedPricingPolicy` (< 20% miejsc → +20%), `PassengerCountPricingPolicy` (>= 5 pasażerów → -10%). `PriceCalculator` — serwis domenowy stosujący aktywne polityki kolejno na basePrice. Zwraca `PriceCalculationResult` (finalPrice Money + lista `appliedRules` stringów z opisem co i dlaczego zostało zastosowane).

**Definition of Ready:**
TASK-002 ukończony (Money VO potrzebne). Zdecydowano kolejność stosowania polityk (addytywne na %). Potwierdzono że polityki nie mogą zejść poniżej 0. Zdecydowano że `appliedRules` to ludzkie opisy dla logowania/debugowania.

**Definition of Done:**
Lot kupowany 31 dni przed wylotem ma cenę bazową * 0.85. Lot kupowany 6 dni przed wylotem ma cenę bazową * 1.30. Kombinacja early bird + 5 pasażerów daje poprawny wynik (oba rabaty). `PriceCalculationResult` zawiera opis każdej zastosowanej polityki. Unit testy pokrywają każdą politykę osobno i kombinacje. Zero importów Symfony w `Pricing/Domain`.

