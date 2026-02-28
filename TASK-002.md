# TASK-002 — Wspólne fundamenty domeny dla wszystkich modułów

**Tytuł biznesowy:** Stworzenie bazowych klas domenowych współdzielonych przez cały system

**Opis biznesowy:**
System wyszukiwania lotów składa się z kilku niezależnych modułów (lotniska, loty, wyszukiwanie, ceny, dostępność). Każdy moduł potrzebuje tych samych fundamentów — sposobu na reprezentowanie pieniędzy, dat, identyfikatorów oraz mechanizmu komunikacji między modułami przez zdarzenia domenowe. Bez wspólnego fundamentu każdy moduł zbuduje własne rozwiązania prowadząc do chaosu i duplikacji.

**Wymagania:**
Abstrakcyjna klasa `AggregateRoot` z mechanizonem rejestrowania i pobierania zdarzeń domenowych. Abstrakcyjna klasa `DomainEvent` z identyfikatorem zdarzenia, datą wystąpienia i identyfikatorem agregatu. Bazowy `DomainException`. Value objecty: `Uuid` (wrapper), `Money` (kwota w groszach + waluta), `DateTimeRange` (zakres dat z walidacją kolejności), `Pagination` (strona + liczba wyników). Interfejsy `CommandBus`, `QueryBus`, `Command`, `Query`, `CommandHandler`, `QueryHandler`. Żadna z tych klas nie może mieć importów z frameworka Symfony.

**Definition of Ready:**
Zdecydowano że Money przechowuje grosze jako int. Zdecydowano jakie waluty obsługuje system (PLN, EUR, USD). Ustalono konwencję nazewnictwa eventów domenowych. Zaakceptowano że Shared Kernel nie ma własnego modułu aplikacyjnego.

**Definition of Done:**
Wszystkie klasy nie zawierają importów Symfony. Unit testy pokrywają walidacje `DateTimeRange` (from < to). Unit testy pokrywają `Money` (dodawanie, odejmowanie, porównywanie). `Uuid` generuje poprawne UUID v4. PHPStan level 8 przechodzi. Żaden inny moduł nie istnieje jeszcze — te klasy są jedyną zależnością którą będą mogły importować.

