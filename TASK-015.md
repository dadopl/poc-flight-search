# TASK-015 — Globalna obsługa błędów i middleware HTTP

**Tytuł biznesowy:** Implementacja spójnej obsługi błędów i middleware dla całego API

**Opis biznesowy:**
Wszystkie odpowiedzi błędów w systemie muszą mieć jednolity format — konsument API nie może dostawać różnych struktur w zależności od modułu który rzucił wyjątek. Każdy request musi być śledzony przez Correlation ID — unikalny identyfikator przepływający przez wszystkie logi umożliwiający debugowanie konkretnego żądania. Format odpowiedzi sukcesu również musi być spójny z metadanymi (timestamp, correlationId).

**Wymagania:**
`ApiExceptionListener` mapujący wyjątki na JSON: `DomainException` → 422, `NotFoundException` → 404, `\InvalidArgumentException` → 400, reszta → 500. Format błędu: `{"error": {"code": "AIRPORT_NOT_FOUND", "message": "...", "details": {}}}`. `CorrelationIdMiddleware` — generuje UUID lub przepuszcza `X-Correlation-Id` z requestu, dostępny przez `CorrelationIdStorage` w DI. `ApiResponseFactory` tworzący: `{"data": ..., "meta": {"correlationId": ..., "timestamp": ...}}`. `RequestValidationMiddleware` sprawdzający Content-Type dla POST/PUT/PATCH.

**Definition of Ready:**
TASK-005 ukończony (pierwsza implementacja ExceptionListener do zastąpienia). Zdecydowano że "code" w błędzie to SCREAMING_SNAKE_CASE nazwa wyjątku. Potwierdzono że 500 nie ujawnia stack trace w produkcji (zmienna APP_ENV). Zaakceptowano format meta w odpowiedziach.

**Definition of Done:**
Każdy wyjątek domenowy ma zmapowany HTTP status i unikalny "code". Odpowiedź 500 nie zawiera stack trace gdy APP_ENV=prod. `X-Correlation-Id` jest w każdej odpowiedzi (zarówno sukces jak i błąd). `ApiResponseFactory` użyty we wszystkich kontrolerach zamiast bezpośrednich Response. Testy jednostkowe `ApiExceptionListener` pokrywają każdy typ wyjątku.

