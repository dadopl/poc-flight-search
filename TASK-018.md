# TASK-018 — Testy integracyjne i scenariusze end-to-end

**Tytuł biznesowy:** Implementacja kompleksowych testów scenariuszowych systemu wyszukiwania lotów

**Opis biznesowy:**
System musi być pokryty testami które weryfikują nie tylko pojedyncze klasy ale całe scenariusze biznesowe — od złożenia zapytania przez HTTP do zwrócenia wyników z cenami. Szczególnie ważny jest scenariusz "brak lotów z Katowic" — musi być udokumentowany testem który jasno pokazuje dlaczego wyniki są puste i który element systemu (AirportDailyFlightLimiter) za to odpowiada.

**Wymagania:**
Trait `DatabaseTestCase` resetujący bazę przez migracje + minimalne fixtures przed każdym testem. Testy integracyjne: `BookSeatsCommandHandlerTest` (invarianty dostępności), `PriceCalculatorIntegrationTest` (kombinacje polityk). Testy funkcjonalne HTTP: pełny flow wyszukiwania POST→polling→GET, scenariusz "brak lotów z Katowic" z asercją na pustą listę i komentarzem wyjaśniającym dlaczego, scenariusz last-minute dopłata, scenariusz niedozwolona zmiana statusu → 409. Raport pokrycia przez `--coverage-html`. Cel: >80% kodu domenowego.

**Definition of Ready:**
TASK-016 ukończony (fixtures potrzebne do testów). Zdecydowano że testy e2e używają WebTestCase (nie Behat). Potwierdzono że osobna baza SQLite dla testów przez `.env.test`. Zaakceptowano że >80% pokrycia dotyczy katalogów `*/Domain/*` i `*/Application/*`.

**Definition of Done:**
`make test` uruchamia całą suitę i przechodzi. Test "brak lotów z Katowic" ma komentarz: `// KTW has daily limit of 2 flights, fixtures load 3 flights from KTW - limiter blocks all results`. Raport pokrycia generuje się w `coverage/`. Pokrycie kodu domenowego > 80%. Żaden test nie zależy od kolejności uruchomienia. CI przechodzi na GitHub Actions.

