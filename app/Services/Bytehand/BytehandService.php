<?php

namespace App\Services\Bytehand;

use App\Services\Bytehand\Entities\SmsSeed;
use Carbon\Carbon;

class BytehandService
{
    public function __construct(public BytehandApi $api) {}

    public function sendSms(
        string $receiver,
        string $text,
        ?Carbon $sendAfter = null,
        ?string $sender = null,
    ): object {
        $seed = new SmsSeed($receiver, $text, $sender, $sendAfter);

        return $this->api->sendSms($seed)->object();
    }
}
