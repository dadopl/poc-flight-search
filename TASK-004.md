# TASK-004 — Moduł zarządzania lotniskami — model domenowy

**Tytuł biznesowy:** Zbudowanie modelu domenowego lotnisk z walidacją kodów IATA i zarządzaniem statusem

**Opis biznesowy:**
System musi wiedzieć które lotniska są aktywne i dostępne jako punkty odlotu lub przylotu. Kod IATA lotniska (np. WAW, KTW, KRK) jest kluczowym identyfikatorem używanym przez linie lotnicze i musi być zawsze poprawny — trzy wielkie litery. Lotnisko może być aktywowane i dezaktywowane — dezaktywowane lotnisko nie powinno pojawiać się w wynikach wyszukiwania. Każde lotnisko ma swoje współrzędne geograficzne potrzebne do przyszłych funkcji (np. wyszukiwanie najbliższego lotniska).

**Wymagania:**
Agregat `Airport` z value objectami: `AirportId`, `IataCode` (dokładnie 3 wielkie litery, walidacja w konstruktorze), `AirportName` (niepusty string max 100 znaków), `Country` (ISO 3166-1 alpha-2), `City`, `GeoCoordinates` (latitude -90/90, longitude -180/180). Metody `activate()` i `deactivate()` rejestrujące eventy. Fabryka statyczna `Airport::create()`. Port `AirportRepository` z metodami findById, findByIataCode, findAllActive. Wyjątki: `AirportNotFoundException`, `InvalidIataCodeException`.

**Definition of Ready:**
Zdecydowano że IataCode to zawsze dokładnie 3 litery. Potwierdzono że Country używa ISO 3166-1 alpha-2. Zaakceptowano że GeoCoordinates są opcjonalne przy tworzeniu (nullable). Zdecydowano jakie eventy rejestruje agregat.

**Definition of Done:**
Próba stworzenia `IataCode` z "KT" lub "katowice" wyrzuca `InvalidIataCodeException`. Próba stworzenia `GeoCoordinates` z latitude=91 wyrzuca wyjątek. `activate()` na aktywnym lotnisku nie rejestruje duplikatu eventu. `pullEvents()` czyści listę po pobraniu. Unit testy pokrywają wszystkie przypadki brzegowe. Zero importów Symfony w katalogu `Airport/Domain`.

