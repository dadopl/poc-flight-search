<?php

declare(strict_types=1);

namespace App\Availability\Presentation\DTO;

use App\Availability\Domain\ValueObject\CabinClass;
use Symfony\Component\HttpFoundation\Request;

final class CheckAvailabilityRequestDTO
{
    public function __construct(
        public readonly ?string $from,
        public readonly ?string $to,
        public readonly ?string $date,
        public readonly ?int $passengers,
        public readonly ?string $cabin,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $passengers = $request->query->get('passengers');

        return new self(
            from: $request->query->get('from'),
            to: $request->query->get('to'),
            date: $request->query->get('date'),
            passengers: $passengers !== null ? (int) $passengers : null,
            cabin: $request->query->get('cabin'),
        );
    }

    /** @return string[] */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->from)) {
            $errors[] = 'Query parameter "from" is required.';
        }

        if (empty($this->to)) {
            $errors[] = 'Query parameter "to" is required.';
        }

        if (empty($this->date)) {
            $errors[] = 'Query parameter "date" is required.';
        } elseif (\DateTimeImmutable::createFromFormat('Y-m-d', $this->date) === false) {
            $errors[] = 'Query parameter "date" must be in format Y-m-d.';
        }

        if ($this->passengers === null) {
            $errors[] = 'Query parameter "passengers" is required.';
        } elseif ($this->passengers < 1 || $this->passengers > 9) {
            $errors[] = 'Query parameter "passengers" must be between 1 and 9.';
        }

        if (empty($this->cabin)) {
            $errors[] = 'Query parameter "cabin" is required.';
        } elseif (!in_array($this->cabin, array_column(CabinClass::cases(), 'value'), true)) {
            $errors[] = sprintf(
                'Query parameter "cabin" must be one of: %s.',
                implode(', ', array_column(CabinClass::cases(), 'value')),
            );
        }

        return $errors;
    }
}
