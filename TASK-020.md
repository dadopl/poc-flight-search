# TASK-020 — Dokumentacja README, decyzje architektoniczne i kolekcja API

**Tytuł biznesowy:** Przygotowanie dokumentacji projektu i kolekcji żądań API do demonstracji systemu

**Opis biznesowy:**
Repozytorium jest publiczne i służy jako materiał demonstracyjny dla POC generatora dokumentacji. Musi zawierać wyczerpującą dokumentację architektury — szczególnie wyjaśnienie dlaczego pewne decyzje zostały podjęte i jakie były alternatywy. Plik `known-limitations.md` celowo opisuje znane ograniczenia systemu — w tym mechanizm limitera KTW który jest głównym przykładem do demonstracji w POC. Kolekcja Bruno pozwala od razu przetestować wszystkie endpointy.

**Wymagania:**
README z: opisem domeny, diagramem modułów w Mermaid, opisem każdego modułu i jego odpowiedzialności, instrukcją uruchomienia (Docker + lokalnie), opisem zmiennych środowiskowych. Plik `docs/architecture-decisions.md`: dlaczego hexagonalna architektura, dlaczego CQRS tylko na agregatach, dlaczego `AirportDailyFlightLimiter` jest serwisem domenowym a nie polityką, dlaczego SearchSession jest agregatem, dlaczego polling a nie WebSocket. Plik `docs/known-limitations.md`: brak autentykacji, synchroniczny event publisher, InMemory → Cache upgrade, szczegółowy opis mechanizmu limitera KTW z przykładem (celowo niski limit 2 jako demonstracja). Kolekcja Bruno z przykładami dla wszystkich endpointów z danymi z fixtures.

**Definition of Ready:**
TASK-019 ukończony. Wszystkie 19 poprzednich tasków zmergowane. Zdecydowano że dokumentacja po angielsku. Potwierdzono że kolekcja Bruno (nie Postman) — open source, bez chmury. Zaakceptowano że `known-limitations.md` jest celowo szczegółowy bo jest materiałem do POC.

**Definition of Done:**
Diagram Mermaid renderuje się poprawnie na GitHub. `docs/known-limitations.md` zawiera sekcję "Airport Daily Flight Limiter" z wyjaśnieniem: konfiguracja limitów, gdzie w kodzie jest sprawdzana, dlaczego KTW ma limit 2, jak zmienić limit. Kolekcja Bruno importuje się i wszystkie żądania działają na świeżych fixtures. README zawiera przykład curl dla scenariusza "brak lotów z Katowic". Repozytorium gotowe jako materiał źródłowy do demonstracji POC.

