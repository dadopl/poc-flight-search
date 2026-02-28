<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AirportControllerTest extends WebTestCase
{
    public function testCreateAirportReturns201(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/airports',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'iataCode' => 'KTW',
                'name'     => 'Katowice Airport',
                'country'  => 'PL',
                'city'     => 'Katowice',
            ]) ?: '',
        );

        $this->assertResponseStatusCodeSame(201);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('ok', $responseData['meta']['status']);
    }

    public function testCreateAirportWithInvalidIataCodeReturns422(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/airports',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'iataCode' => 'invalid',
                'name'     => 'Test Airport',
                'country'  => 'PL',
                'city'     => 'Katowice',
            ]) ?: '',
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateAirportWithMissingFieldsReturns422(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/airports',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['iataCode' => 'KTW']) ?: '',
        );

        $this->assertResponseStatusCodeSame(422);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testListActiveAirportsReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/airports');

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('ok', $responseData['meta']['status']);
        $this->assertIsArray($responseData['data']);
    }

    public function testGetAirportByNonExistentIataCodeReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/airports/ZZZ');

        $this->assertResponseStatusCodeSame(404);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('error', $responseData['meta']['status']);
    }

    public function testGetAirportWithInvalidIataCodeReturns422(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/airports/INVALID');

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateThenGetAirport(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/airports',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'iataCode' => 'WAW',
                'name'     => 'Warsaw Chopin Airport',
                'country'  => 'PL',
                'city'     => 'Warsaw',
            ]) ?: '',
        );
        $this->assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/airports/WAW');
        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('WAW', $responseData['data']['iataCode']);
        $this->assertSame('Warsaw Chopin Airport', $responseData['data']['name']);
    }
}
