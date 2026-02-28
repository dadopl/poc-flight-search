<?php

declare(strict_types=1);

namespace App\Tests\Availability\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AvailabilityControllerTest extends WebTestCase
{
    private const FLIGHT_ID = '550e8400-e29b-41d4-a716-446655440010';

    private function createFlightAndAvailability(
        \Symfony\Bundle\FrameworkBundle\KernelBrowser $client,
        string $flightId = self::FLIGHT_ID,
        string $flightNumber = 'LO099',
        string $departureAirportIata = 'WAW',
        string $arrivalAirportIata = 'GDN',
    ): void {
        // Create airports
        foreach ([$departureAirportIata, $arrivalAirportIata] as $iata) {
            $client->request(
                'POST',
                '/api/airports',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode([
                    'iataCode' => $iata,
                    'name'     => $iata . ' Airport',
                    'country'  => 'PL',
                    'city'     => $iata . ' City',
                ]) ?: '',
            );
        }

        // Create flight
        $client->request(
            'POST',
            '/api/flights',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'flightNumber'        => $flightNumber,
                'departureAirportIata' => $departureAirportIata,
                'arrivalAirportIata'   => $arrivalAirportIata,
                'departureTime'        => '2024-12-25 10:00:00',
                'arrivalTime'          => '2024-12-25 12:00:00',
                'aircraftModel'        => 'Boeing 737',
                'aircraftTotalSeats'   => 180,
            ]) ?: '',
        );

        // Get the flight ID from the database via another request
        // Initialize availability using our known flightId if the flight was created
        $client->request(
            'POST',
            '/api/flights/' . $flightNumber . '/availability/initialize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'totalSeats'               => 180,
                'cabinClass'               => 'ECONOMY',
                'minimumAvailableThreshold' => 10,
            ]) ?: '',
        );
    }

    public function testCheckAvailabilityReturnsMissingParamsError(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/availability/check');

        $this->assertResponseStatusCodeSame(422);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('error', $responseData['meta']['status']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertNotEmpty($responseData['errors']);
    }

    public function testCheckAvailabilityWithInvalidDateReturns422(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/availability/check?from=KTW&to=WAW&date=invalid-date&passengers=2&cabin=ECONOMY',
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCheckAvailabilityWithInvalidCabinReturns422(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/availability/check?from=KTW&to=WAW&date=2024-12-25&passengers=2&cabin=INVALID',
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCheckAvailabilityWithInvalidPassengersReturns422(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/availability/check?from=KTW&to=WAW&date=2024-12-25&passengers=0&cabin=ECONOMY',
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCheckAvailabilityReturnsEmptyListWhenNoFlightsExist(): void
    {
        $client = static::createClient();

        // Create airports first
        foreach (['WAW', 'GDN'] as $iata) {
            $client->request(
                'POST',
                '/api/airports',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode([
                    'iataCode' => $iata,
                    'name'     => $iata . ' Airport',
                    'country'  => 'PL',
                    'city'     => $iata . ' City',
                ]) ?: '',
            );
        }

        $client->request(
            'GET',
            '/api/availability/check?from=WAW&to=GDN&date=2024-12-25&passengers=1&cabin=ECONOMY',
        );

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('ok', $responseData['meta']['status']);
        $this->assertSame([], $responseData['data']);
    }

    public function testInitializeAvailabilityWithMissingFieldsReturns422(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/flights/some-flight-id/availability/initialize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['totalSeats' => 180]) ?: '',
        );

        $this->assertResponseStatusCodeSame(422);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testInitializeAvailabilityWithInvalidCabinReturns422(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/flights/some-flight-id/availability/initialize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'totalSeats' => 180,
                'cabinClass' => 'INVALID',
            ]) ?: '',
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testInitializeAvailabilityWithNegativeTotalSeatsReturns422(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/flights/some-flight-id/availability/initialize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'totalSeats' => -1,
                'cabinClass' => 'ECONOMY',
            ]) ?: '',
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testGetAvailabilityReturns404WhenNotFound(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/flights/non-existent-flight-id/availability',
        );

        $this->assertResponseStatusCodeSame(404);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('error', $responseData['meta']['status']);
    }

    public function testCheckAvailabilityReturnsMissingFromParam(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/availability/check?to=WAW&date=2024-12-25&passengers=2&cabin=ECONOMY',
        );

        $this->assertResponseStatusCodeSame(422);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertStringContainsString('from', implode(' ', $responseData['errors']));
    }
}
