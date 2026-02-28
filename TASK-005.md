# TASK-005 — Moduł zarządzania lotniskami — API REST

**Tytuł biznesowy:** Udostępnienie API do zarządzania lotniskami i pobierania ich listy

**Opis biznesowy:**
Administratorzy systemu muszą móc dodawać nowe lotniska, aktywować i dezaktywować istniejące przez REST API. System wyszukiwania lotów potrzebuje pobierać listę aktywnych lotnisk jako punktów odlotu i przylotu dostępnych dla użytkownika. Odpowiedzi API muszą być spójne z resztą systemu i zawierać tylko dane niezbędne dla konsumenta.

**Wymagania:**
Komendy z handlerami: `CreateAirportCommand`, `ActivateAirportCommand`, `DeactivateAirportCommand`. Zapytania z handlerami: `GetAirportQuery` (po id), `ListActiveAirportsQuery`. Encja Doctrine oddzielona od agregatu domenowego z dedykowanym mapperem. Endpointy: `POST /api/airports`, `GET /api/airports`, `GET /api/airports/{iataCode}`. Walidacja requestów przez Symfony Validator na DTO. Spójny format odpowiedzi przez `ApiResponseFactory`. Globalny `ExceptionListener` mapujący wyjątki domenowe na HTTP.

**Definition of Ready:**
TASK-003 i TASK-004 ukończone i zmergowane. Zdecydowano format odpowiedzi API (struktura JSON). Potwierdzono że encja Doctrine nie może być agregatem domenowym. Zaakceptowano że mapowanie encja↔agregat jest jawnym kodem a nie magią Doctrine.

**Definition of Done:**
`POST /api/airports` z niepoprawnym kodem IATA zwraca 422 z czytelnym komunikatem. `GET /api/airports` zwraca tylko aktywne lotniska. `GET /api/airports/NIEISTNIEJACY` zwraca 404. Testy funkcjonalne WebTestCase pokrywają wszystkie endpointy. Doctrine poprawnie persystuje i odtwarza agregat przez mapper. PHPStan przechodzi.

