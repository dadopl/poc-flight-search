<?php

declare(strict_types=1);

namespace App\Tests\Flight\Domain;

use App\Flight\Domain\Exception\InvalidFlightStatusTransitionException;
use App\Flight\Domain\Service\FlightStatusTransitionPolicy;
use App\Flight\Domain\ValueObject\FlightStatus;
use PHPUnit\Framework\TestCase;

class FlightStatusTransitionPolicyTest extends TestCase
{
    /**
     * @dataProvider allowedTransitions
     */
    public function testAllowsValidTransition(FlightStatus $from, FlightStatus $to): void
    {
        // Should not throw
        FlightStatusTransitionPolicy::assertCanTransition($from, $to);
        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider disallowedTransitions
     */
    public function testDisallowsInvalidTransition(FlightStatus $from, FlightStatus $to): void
    {
        $this->expectException(InvalidFlightStatusTransitionException::class);

        FlightStatusTransitionPolicy::assertCanTransition($from, $to);
    }

    public function testGetAllowedTransitionsForScheduled(): void
    {
        $allowed = FlightStatusTransitionPolicy::getAllowedTransitions(FlightStatus::SCHEDULED);

        $this->assertContains(FlightStatus::BOARDING, $allowed);
        $this->assertContains(FlightStatus::DELAYED, $allowed);
        $this->assertContains(FlightStatus::CANCELLED, $allowed);
        $this->assertCount(3, $allowed);
    }

    public function testGetAllowedTransitionsForBoarding(): void
    {
        $allowed = FlightStatusTransitionPolicy::getAllowedTransitions(FlightStatus::BOARDING);

        $this->assertContains(FlightStatus::DEPARTED, $allowed);
        $this->assertContains(FlightStatus::CANCELLED, $allowed);
        $this->assertCount(2, $allowed);
    }

    public function testGetAllowedTransitionsForDeparted(): void
    {
        $allowed = FlightStatusTransitionPolicy::getAllowedTransitions(FlightStatus::DEPARTED);

        $this->assertContains(FlightStatus::ARRIVED, $allowed);
        $this->assertCount(1, $allowed);
    }

    public function testGetAllowedTransitionsForArrived(): void
    {
        $allowed = FlightStatusTransitionPolicy::getAllowedTransitions(FlightStatus::ARRIVED);

        $this->assertEmpty($allowed);
    }

    public function testGetAllowedTransitionsForCancelled(): void
    {
        $allowed = FlightStatusTransitionPolicy::getAllowedTransitions(FlightStatus::CANCELLED);

        $this->assertEmpty($allowed);
    }

    public function testGetAllowedTransitionsForDelayed(): void
    {
        $allowed = FlightStatusTransitionPolicy::getAllowedTransitions(FlightStatus::DELAYED);

        $this->assertContains(FlightStatus::BOARDING, $allowed);
        $this->assertContains(FlightStatus::CANCELLED, $allowed);
        $this->assertCount(2, $allowed);
    }

    /** @return array<string, array{FlightStatus, FlightStatus}> */
    public static function allowedTransitions(): array
    {
        return [
            'SCHEDULED → BOARDING'  => [FlightStatus::SCHEDULED, FlightStatus::BOARDING],
            'SCHEDULED → DELAYED'   => [FlightStatus::SCHEDULED, FlightStatus::DELAYED],
            'SCHEDULED → CANCELLED' => [FlightStatus::SCHEDULED, FlightStatus::CANCELLED],
            'BOARDING → DEPARTED'   => [FlightStatus::BOARDING, FlightStatus::DEPARTED],
            'BOARDING → CANCELLED'  => [FlightStatus::BOARDING, FlightStatus::CANCELLED],
            'DEPARTED → ARRIVED'    => [FlightStatus::DEPARTED, FlightStatus::ARRIVED],
            'DELAYED → BOARDING'    => [FlightStatus::DELAYED, FlightStatus::BOARDING],
            'DELAYED → CANCELLED'   => [FlightStatus::DELAYED, FlightStatus::CANCELLED],
        ];
    }

    /** @return array<string, array{FlightStatus, FlightStatus}> */
    public static function disallowedTransitions(): array
    {
        return [
            'SCHEDULED → DEPARTED'  => [FlightStatus::SCHEDULED, FlightStatus::DEPARTED],
            'SCHEDULED → ARRIVED'   => [FlightStatus::SCHEDULED, FlightStatus::ARRIVED],
            'SCHEDULED → SCHEDULED' => [FlightStatus::SCHEDULED, FlightStatus::SCHEDULED],
            'BOARDING → SCHEDULED'  => [FlightStatus::BOARDING, FlightStatus::SCHEDULED],
            'BOARDING → DELAYED'    => [FlightStatus::BOARDING, FlightStatus::DELAYED],
            'BOARDING → ARRIVED'    => [FlightStatus::BOARDING, FlightStatus::ARRIVED],
            'BOARDING → BOARDING'   => [FlightStatus::BOARDING, FlightStatus::BOARDING],
            'DEPARTED → SCHEDULED'  => [FlightStatus::DEPARTED, FlightStatus::SCHEDULED],
            'DEPARTED → BOARDING'   => [FlightStatus::DEPARTED, FlightStatus::BOARDING],
            'DEPARTED → CANCELLED'  => [FlightStatus::DEPARTED, FlightStatus::CANCELLED],
            'DEPARTED → DELAYED'    => [FlightStatus::DEPARTED, FlightStatus::DELAYED],
            'DEPARTED → DEPARTED'   => [FlightStatus::DEPARTED, FlightStatus::DEPARTED],
            'ARRIVED → SCHEDULED'   => [FlightStatus::ARRIVED, FlightStatus::SCHEDULED],
            'ARRIVED → BOARDING'    => [FlightStatus::ARRIVED, FlightStatus::BOARDING],
            'ARRIVED → DEPARTED'    => [FlightStatus::ARRIVED, FlightStatus::DEPARTED],
            'ARRIVED → CANCELLED'   => [FlightStatus::ARRIVED, FlightStatus::CANCELLED],
            'ARRIVED → DELAYED'     => [FlightStatus::ARRIVED, FlightStatus::DELAYED],
            'ARRIVED → ARRIVED'     => [FlightStatus::ARRIVED, FlightStatus::ARRIVED],
            'CANCELLED → SCHEDULED' => [FlightStatus::CANCELLED, FlightStatus::SCHEDULED],
            'CANCELLED → BOARDING'  => [FlightStatus::CANCELLED, FlightStatus::BOARDING],
            'CANCELLED → DEPARTED'  => [FlightStatus::CANCELLED, FlightStatus::DEPARTED],
            'CANCELLED → ARRIVED'   => [FlightStatus::CANCELLED, FlightStatus::ARRIVED],
            'CANCELLED → DELAYED'   => [FlightStatus::CANCELLED, FlightStatus::DELAYED],
            'CANCELLED → CANCELLED' => [FlightStatus::CANCELLED, FlightStatus::CANCELLED],
            'DELAYED → SCHEDULED'   => [FlightStatus::DELAYED, FlightStatus::SCHEDULED],
            'DELAYED → DEPARTED'    => [FlightStatus::DELAYED, FlightStatus::DEPARTED],
            'DELAYED → ARRIVED'     => [FlightStatus::DELAYED, FlightStatus::ARRIVED],
            'DELAYED → DELAYED'     => [FlightStatus::DELAYED, FlightStatus::DELAYED],
        ];
    }
}
