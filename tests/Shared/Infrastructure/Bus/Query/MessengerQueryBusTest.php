<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Bus\Query;

use App\Shared\Domain\Bus\Query\Query;
use App\Shared\Domain\Bus\Query\QueryHandler;
use App\Shared\Infrastructure\Bus\Query\MessengerQueryBus;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class MessengerQueryBusTest extends TestCase
{
    public function testQueryReturnsHandlerResult(): void
    {
        $query = new class() implements Query {};
        $queryClass = $query::class;

        $handler = new class() implements QueryHandler {
            public function __invoke(object $query): string
            {
                return 'query_result';
            }
        };

        $bus = $this->buildBus($queryClass, $handler);
        $queryBus = new MessengerQueryBus($bus);

        $result = $queryBus->ask($query);

        $this->assertSame('query_result', $result);
    }

    public function testQueryIsRoutedToCorrectHandler(): void
    {
        $query = new class() implements Query {};
        $queryClass = $query::class;

        $handler = new class() implements QueryHandler {
            public bool $called = false;

            public function __invoke(object $query): bool
            {
                $this->called = true;

                return true;
            }
        };

        $bus = $this->buildBus($queryClass, $handler);
        $queryBus = new MessengerQueryBus($bus);

        $queryBus->ask($query);

        $this->assertTrue($handler->called);
    }

    public function testExceptionFromHandlerPropagates(): void
    {
        $query = new class() implements Query {};
        $queryClass = $query::class;

        $handler = new class() implements QueryHandler {
            public function __invoke(object $query): never
            {
                throw new RuntimeException('query handler error');
            }
        };

        $bus = $this->buildBus($queryClass, $handler);
        $queryBus = new MessengerQueryBus($bus);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('query handler error');

        $queryBus->ask($query);
    }

    private function buildBus(string $messageClass, object $handler): MessageBus
    {
        return new MessageBus([
            new HandleMessageMiddleware(
                new HandlersLocator([$messageClass => [$handler]]),
            ),
        ]);
    }
}
