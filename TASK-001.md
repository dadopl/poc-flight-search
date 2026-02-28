# TASK-001 — Uruchomienie projektu wyszukiwarki lotów

**Tytuł biznesowy:** Przygotowanie środowiska deweloperskiego systemu wyszukiwania lotów

**Opis biznesowy:**
Zespół potrzebuje wspólnej, ustandaryzowanej bazy projektu aby wszyscy deweloperzy mogli pracować w identycznym środowisku. Brak spójnej konfiguracji powoduje problemy z odtwarzalnością błędów i spowalnia onboarding nowych osób. Projekt musi od pierwszego dnia wymuszać jakość kodu przez automatyczne narzędzia analizy statycznej i testy.

**Wymagania:**
Projekt Symfony 7.4 z PHP 8.4 jako twardym wymaganiem. Konfiguracja bazy danych SQLite dla środowiska deweloperskiego i PostgreSQL dla produkcji przez zmienne środowiskowe. PHPStan na poziomie 8 jako blokada CI. CodeSniffer ze standardem PSR-12. PHPUnit skonfigurowany z osobną bazą testową. Makefile z komendami `make test`, `make stan`, `make cs`, `make migrate`. Publiczne repozytorium GitHub z README opisującym uruchomienie.

**Definition of Ready:**
Nazwa projektu zatwierdzona przez zespół. Dostęp do GitHub organizacji przyznany. Zdecydowano że SQLite na dev, PostgreSQL na prod. Wybrano PHP 8.4 jako minimalną wersję.

**Definition of Done:**
`bin/console` działa bez błędów. PHPUnit uruchamia pustą suitę i przechodzi. PHPStan level 8 nie zgłasza błędów na pustym projekcie. CodeSniffer nie zgłasza naruszeń. README zawiera instrukcję uruchomienia. CI na GitHub Actions przechodzi.

