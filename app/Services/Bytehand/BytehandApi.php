<?php

namespace App\Services\Bytehand;

use App\Services\Bytehand\Entities\SmsSeed;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BytehandApi
{
    public function querySmsSenders(): Response
    {
        return Http::bytehand()
            ->get('sms/senders');
    }

    public function sendSms(SmsSeed|array $data): Response
    {
        return Http::bytehand()
            ->post('sms/messages', $data);
    }
}
