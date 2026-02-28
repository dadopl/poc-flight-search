<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Event;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Event\DomainEventListener;
use App\Shared\Infrastructure\Event\SynchronousDomainEventPublisher;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SynchronousDomainEventPublisherTest extends TestCase
{
    public function testListenerIsCalledForSubscribedEvent(): void
    {
        $event = new TestDomainEvent('agg-1');
        $listener = new class() implements DomainEventListener {
            public bool $called = false;

            public function handle(DomainEvent $event): void
            {
                $this->called = true;
            }

            public function subscribedTo(): string
            {
                return TestDomainEvent::class;
            }
        };

        $publisher = new SynchronousDomainEventPublisher([$listener]);
        $publisher->publish($event);

        $this->assertTrue($listener->called);
    }

    public function testListenerIsNotCalledForUnsubscribedEvent(): void
    {
        $event = new TestDomainEvent('agg-1');
        $listener = new class() implements DomainEventListener {
            public bool $called = false;

            public function handle(DomainEvent $event): void
            {
                $this->called = true;
            }

            public function subscribedTo(): string
            {
                return AnotherTestDomainEvent::class;
            }
        };

        $publisher = new SynchronousDomainEventPublisher([$listener]);
        $publisher->publish($event);

        $this->assertFalse($listener->called);
    }

    public function testMultipleListenersAreEachCalled(): void
    {
        $event = new TestDomainEvent('agg-1');

        $firstListener = new class() implements DomainEventListener {
            public int $callCount = 0;

            public function handle(DomainEvent $event): void
            {
                ++$this->callCount;
            }

            public function subscribedTo(): string
            {
                return TestDomainEvent::class;
            }
        };

        $secondListener = new class() implements DomainEventListener {
            public int $callCount = 0;

            public function handle(DomainEvent $event): void
            {
                ++$this->callCount;
            }

            public function subscribedTo(): string
            {
                return TestDomainEvent::class;
            }
        };

        $publisher = new SynchronousDomainEventPublisher([$firstListener, $secondListener]);
        $publisher->publish($event);

        $this->assertSame(1, $firstListener->callCount);
        $this->assertSame(1, $secondListener->callCount);
    }

    public function testMultipleEventsDispatchedToCorrectListeners(): void
    {
        $testEvent = new TestDomainEvent('agg-1');
        $anotherEvent = new AnotherTestDomainEvent('agg-2');

        $testListener = new class() implements DomainEventListener {
            public int $callCount = 0;

            public function handle(DomainEvent $event): void
            {
                ++$this->callCount;
            }

            public function subscribedTo(): string
            {
                return TestDomainEvent::class;
            }
        };

        $anotherListener = new class() implements DomainEventListener {
            public int $callCount = 0;

            public function handle(DomainEvent $event): void
            {
                ++$this->callCount;
            }

            public function subscribedTo(): string
            {
                return AnotherTestDomainEvent::class;
            }
        };

        $publisher = new SynchronousDomainEventPublisher([$testListener, $anotherListener]);
        $publisher->publish($testEvent, $anotherEvent);

        $this->assertSame(1, $testListener->callCount);
        $this->assertSame(1, $anotherListener->callCount);
    }

    public function testExceptionFromListenerPropagates(): void
    {
        $event = new TestDomainEvent('agg-1');
        $listener = new class() implements DomainEventListener {
            public function handle(DomainEvent $event): void
            {
                throw new RuntimeException('listener error');
            }

            public function subscribedTo(): string
            {
                return TestDomainEvent::class;
            }
        };

        $publisher = new SynchronousDomainEventPublisher([$listener]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('listener error');

        $publisher->publish($event);
    }
}

final class TestDomainEvent extends DomainEvent {}
final class AnotherTestDomainEvent extends DomainEvent {}
