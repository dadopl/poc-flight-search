<?php

declare(strict_types=1);

namespace App\Search\Domain\Aggregate;

use App\Search\Domain\Enum\SearchStatus;
use App\Search\Domain\ValueObject\SearchCriteria;
use App\Search\Domain\ValueObject\SearchFilters;
use App\Search\Domain\ValueObject\SearchSessionId;
use App\Shared\Domain\AggregateRoot;
use DomainException;

final class SearchSession extends AggregateRoot
{
    private SearchStatus $status;
    private ?int $resultCount = null;
    private ?string $failureReason = null;

    private function __construct(
        private readonly SearchSessionId $id,
        private readonly SearchCriteria $criteria,
        private readonly SearchFilters $filters,
    ) {
        $this->status = SearchStatus::PENDING;
    }

    public static function initiate(
        SearchSessionId $id,
        SearchCriteria $criteria,
        SearchFilters $filters,
    ): self {
        return new self($id, $criteria, $filters);
    }

    public function start(): void
    {
        if ($this->status !== SearchStatus::PENDING) {
            throw new DomainException(
                sprintf('Cannot start search session in status "%s". Expected PENDING.', $this->status->value),
            );
        }

        $this->status = SearchStatus::PROCESSING;
    }

    public function complete(int $resultCount): void
    {
        if ($this->status !== SearchStatus::PROCESSING) {
            throw new DomainException(
                sprintf('Cannot complete search session in status "%s". Expected PROCESSING.', $this->status->value),
            );
        }

        $this->status = SearchStatus::COMPLETED;
        $this->resultCount = $resultCount;
    }

    public function fail(string $reason): void
    {
        if ($this->status === SearchStatus::COMPLETED) {
            throw new DomainException(
                'Cannot fail a search session that is already COMPLETED.',
            );
        }

        $this->status = SearchStatus::FAILED;
        $this->failureReason = $reason;
    }

    public function getId(): SearchSessionId
    {
        return $this->id;
    }

    public function getCriteria(): SearchCriteria
    {
        return $this->criteria;
    }

    public function getFilters(): SearchFilters
    {
        return $this->filters;
    }

    public function getStatus(): SearchStatus
    {
        return $this->status;
    }

    public function getResultCount(): ?int
    {
        return $this->resultCount;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }
}
