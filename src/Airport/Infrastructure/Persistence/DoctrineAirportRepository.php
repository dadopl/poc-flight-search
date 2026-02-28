<?php

declare(strict_types=1);

namespace App\Airport\Infrastructure\Persistence;

use App\Airport\Domain\Airport;
use App\Airport\Domain\AirportRepository;
use App\Airport\Domain\IataCode;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineAirportRepository implements AirportRepository
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function findByIataCode(IataCode $iataCode): ?Airport
    {
        return $this->entityManager->find(Airport::class, $iataCode->getValue());
    }

    /** @return Airport[] */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Airport::class)->findAll();
    }

    public function save(Airport $airport): void
    {
        $this->entityManager->persist($airport);
        $this->entityManager->flush();
    }

    public function delete(Airport $airport): void
    {
        $this->entityManager->remove($airport);
        $this->entityManager->flush();
    }
}
