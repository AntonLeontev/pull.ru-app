<?php

namespace App\Services\Planfact\Entities;

use Carbon\Carbon;
use JsonSerializable;

readonly class Outcome implements JsonSerializable
{
    public function __construct(
        public Carbon $date,
        public float $value,
        public int $accountId,
        public bool $isCommitted = false,
        public bool $isCalculationCommitted = false,
        public ?int $contrAgentId = null,
        public ?int $projectId = null,
        public ?int $operationCategoryId = null,
        public ?string $externalId = null,
        public ?string $comment = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'operationDate' => $this->date->format('Y-m-d'),
            'accountId' => $this->accountId,
            'isCommitted' => $this->isCommitted,
            'contrAgentId' => $this->contrAgentId,
            'comment' => $this->comment,
            'items' => [
                [
                    'calculationDate' => $this->date->format('Y-m-d'),
                    'isCalculationCommitted' => $this->isCalculationCommitted,
                    'operationCategoryId' => $this->operationCategoryId,
                    'contrAgentId' => $this->contrAgentId,
                    'projectId' => $this->projectId,
                    'value' => $this->value,
                ],
            ],
            'externalId' => $this->externalId,
        ];
    }
}
