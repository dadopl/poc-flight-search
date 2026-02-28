<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Bus\Command;

use App\Shared\Domain\Bus\Command\Command;
use App\Shared\Domain\Bus\Command\CommandHandler;
use App\Shared\Infrastructure\Bus\Command\MessengerCommandBus;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class MessengerCommandBusTest extends TestCase
{
    public function testCommandIsRoutedToHandler(): void
    {
        $command = new class() implements Command {};
        $commandClass = $command::class;

        $handler = new class() implements CommandHandler {
            public bool $handled = false;

            public function __invoke(object $command): void
            {
                $this->handled = true;
            }
        };

        $bus = $this->buildBus($commandClass, $handler);
        $commandBus = new MessengerCommandBus($bus);

        $commandBus->execute($command);

        $this->assertTrue($handler->handled);
    }

    public function testExceptionFromHandlerPropagates(): void
    {
        $command = new class() implements Command {};
        $commandClass = $command::class;

        $handler = new class() implements CommandHandler {
            public function __invoke(object $command): void
            {
                throw new RuntimeException('handler error');
            }
        };

        $bus = $this->buildBus($commandClass, $handler);
        $commandBus = new MessengerCommandBus($bus);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('handler error');

        $commandBus->execute($command);
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
