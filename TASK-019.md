# TASK-019 — CI/CD pipeline i szablony Pull Requestów

**Tytuł biznesowy:** Wdrożenie pipeline'u CI i procesu code review wymuszającego jakość opisów PR

**Opis biznesowy:**
Każda zmiana kodu musi przejść automatyczną weryfikację przed mergem — analiza statyczna, standardy kodu i testy muszą być zielone. Kluczowe dla POC generatora dokumentacji: Pull Requesty muszą mieć wypełniony opis według szablonu zawierającego sekcje o decyzjach architektonicznych i wpływie na inne moduły. PR bez dobrego opisu = zły wsad do generatora dokumentacji = słaba dokumentacja. Pipeline wymusza wypełnienie szablonu jako blokadę merge'a.

**Wymagania:**
Workflow `ci.yml`: checkout → PHP 8.4 z extensions (pdo_sqlite, intl) → `composer install` → `make stan` → `make cs` → `make test` → upload coverage artifact. Workflow `pr-check.yml`: weryfikuje że opis PR ma minimum 200 znaków i zawiera wymagane sekcje. Szablon `.github/pull_request_template.md` z sekcjami: "Co zostało zrobione" (opis biznesowy zmiany), "Decyzje architektoniczne" (dlaczego tak a nie inaczej), "Odrzucone alternatywy" (co rozważano), "Wpływ na inne moduły", "Jak testować", "Znane ograniczenia". Label'e: `module:flight`, `module:search`, `module:pricing`, `module:availability`, `module:airport`, `module:shared`.

**Definition of Ready:**
TASK-018 ukończony. GitHub Actions dostępne w repozytorium. Zdecydowano że minimum 200 znaków opisu jako blokada. Potwierdzono że szablon PR jest po angielsku (kod po angielsku, README po angielsku). Zaakceptowano że label musi być przypisany przez autora PR.

**Definition of Done:**
PR bez wypełnionych sekcji szablonu dostaje failed check `pr-check`. Badge CI widoczny w README (zielony). Każdy z 18 poprzednich tasków ma PR z wypełnionym szablonem — są to materiały do demonstracji POC generatora dokumentacji. `make stan && make cs && make test` przechodzi lokalnie i na CI identycznie.

