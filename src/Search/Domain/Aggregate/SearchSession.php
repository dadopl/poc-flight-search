<?php

declare(strict_types=1);

namespace App\Search\Domain\Aggregate;

use App\Search\Domain\Event\SearchSessionCompleted;
use App\Search\Domain\Event\SearchSessionFailed;
use App\Search\Domain\Event\SearchSessionStarted;
use App\Search\Domain\Exception\InvalidSearchSessionStateException;
use App\Search\Domain\ValueObject\SearchCriteria;
use App\Search\Domain\ValueObject\SearchFilters;
use App\Search\Domain\ValueObject\SearchSessionId;
use App\Search\Domain\ValueObject\SearchStatus;
use App\Shared\Domain\AggregateRoot;

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

    public static function create(
        SearchSessionId $id,
        SearchCriteria $criteria,
        SearchFilters $filters,
    ): self {
        return new self($id, $criteria, $filters);
    }

    public function start(): void
    {
        if ($this->status !== SearchStatus::PENDING) {
            throw InvalidSearchSessionStateException::cannotStart($this->status->value);
        }

        $this->status = SearchStatus::PROCESSING;
        $this->recordEvent(new SearchSessionStarted($this->id->getValue()));
    }

    public function complete(int $resultCount): void
    {
        if ($this->status !== SearchStatus::PROCESSING) {
            throw InvalidSearchSessionStateException::cannotComplete($this->status->value);
        }

        $this->status = SearchStatus::COMPLETED;
        $this->resultCount = $resultCount;
        $this->recordEvent(new SearchSessionCompleted($this->id->getValue(), $resultCount));
    }

    public function fail(string $reason): void
    {
        if ($this->status !== SearchStatus::PROCESSING) {
            throw InvalidSearchSessionStateException::cannotFail($this->status->value);
        }

        $this->status = SearchStatus::FAILED;
        $this->failureReason = $reason;
        $this->recordEvent(new SearchSessionFailed($this->id->getValue(), $reason));
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
