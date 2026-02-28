# TASK-003 — Magistrala komend i zdarzeń jako infrastruktura komunikacji

**Tytuł biznesowy:** Implementacja systemu magistrali umożliwiającego komunikację między modułami systemu

**Opis biznesowy:**
Moduły systemu muszą komunikować się ze sobą nie znając swoich szczegółów implementacyjnych. Gdy moduł wyszukiwania potrzebuje sprawdzić dostępność miejsc — nie powinien bezpośrednio wywoływać serwisów modułu dostępności. Magistrala komend i zapytań rozwiązuje ten problem zapewniając luźne powiązanie. Zdarzenia domenowe muszą być publikowane synchronicznie (na etapie POC) z możliwością przyszłego przejścia na Kafka.

**Wymagania:**
Implementacja `MessengerCommandBus` i `MessengerQueryBus` przez Symfony Messenger jako adaptery portów z TASK-002. CommandBus nie zwraca wartości. QueryBus zwraca wynik handlera. Konfiguracja dwóch transportów in-memory dla dev. Automatyczne tagowanie handlerów przez DI. `SynchronousDomainEventPublisher` wywołujący listenery w tej samej transakcji. Konfiguracja `services.yaml` z autowiring i autoconfigure.

**Definition of Ready:**
TASK-002 ukończony i zmergowany. Zdecydowano że Symfony Messenger jako implementacja magistrali. Zdecydowano że eventy domenowe synchroniczne na POC. Zaakceptowano że przyszła migracja na Kafka wymaga tylko zamiany adaptera.

**Definition of Done:**
Testy integracyjne potwierdzają że komenda wysłana przez CommandBus trafia do właściwego handlera. Testy potwierdzają że QueryBus zwraca wynik handlera. `SynchronousDomainEventPublisher` wywołuje wszystkich listenerów zarejestrowanych na dany typ eventu. Błąd w handlerze propaguje się jako wyjątek. PHPStan przechodzi.

