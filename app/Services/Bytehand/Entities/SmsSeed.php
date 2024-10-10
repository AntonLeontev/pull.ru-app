<?php

namespace App\Services\Bytehand\Entities;

use Carbon\Carbon;
use JsonSerializable;

class SmsSeed implements JsonSerializable
{
    public function __construct(
        private string $receiver,
        private string $text,
        private ?string $sender = null,
        private ?Carbon $sendAfter = null,
    ) {
        if (is_null($sender)) {
            $this->sender = config('services.bytehand.sender');
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'receiver' => $this->receiver,
            'text' => $this->text,
            'sender' => $this->sender,
            'send_after' => $this->sendAfter?->toIso8601String(),
        ];
    }
}
