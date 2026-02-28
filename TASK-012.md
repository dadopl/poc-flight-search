# TASK-012 — Moduł wyszukiwania — API wyszukiwania lotów

**Tytuł biznesowy:** Udostępnienie głównego API wyszukiwania lotów z dokumentacją OpenAPI

**Opis biznesowy:**
Użytkownik systemu (aplikacja frontendowa lub inny serwis) inicjuje wyszukiwanie przez POST, dostaje sessionId i następnie polling'uje GET aby sprawdzić czy wyniki są gotowe. To podejście asynchroniczne pozwala na przyszłą optymalizację (Kafka, WebSocket) bez zmiany kontraktu API. Dokumentacja OpenAPI / Swagger musi być dostępna pod `/api/doc` — jest kluczowa dla zespołów konsumujących API.

**Wymagania:**
`POST /api/search` — przyjmuje `SearchRequestDTO` z pełną walidacją: format IATA (Length=3, uppercase), date format Y-m-d nie w przeszłości, passengers 1-9, cabinClass z enuma, filters opcjonalne. Zwraca 202 z sessionId i linkiem do wyników. `GET /api/search/{sessionId}/results` — zwraca status + wyniki gdy COMPLETED, 202 gdy PROCESSING, 422 gdy FAILED z powodem. Dokumentacja OpenAPI przez atrybuty `#[OA\...]` na kontrolerach i DTO. `nelmio/api-doc-bundle` ze Swagger UI pod `/api/doc`. Rate limiting 100 req/min per IP przez `symfony/rate-limiter`.

**Definition of Ready:**
TASK-011 ukończony. Zdecydowano że polling a nie WebSocket na POC. Potwierdzono że 202 zwracany gdy wyniki jeszcze nie gotowe. Zaakceptowano że Swagger UI dostępny bez autentykacji (POC).

**Definition of Done:**
`POST /api/search` z datą w przeszłości zwraca 422 z opisem błędu. `GET /api/search/{nieistniejacyId}` zwraca 404. Swagger UI ładuje się pod `/api/doc` i pokazuje wszystkie endpointy. Dokumentacja OpenAPI zawiera przykładowe request/response dla search. Rate limiter zwraca 429 po przekroczeniu limitu. Testy funkcjonalne pokrywają pełny flow polling'u.

