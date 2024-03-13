<?php

namespace App\Services\Cloudpayments\Entities;

use JsonSerializable;

class PurveyorData implements JsonSerializable
{
    public function __construct(
        public string $name,
        public string $inn,
        public ?string $phone = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'Name' => $this->name,
            'Inn' => $this->inn,
            'Phone' => $this->phone,
        ];
    }
}
