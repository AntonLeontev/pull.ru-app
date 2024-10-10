<?php

namespace App\Services\Bytehand\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class BytehandException extends Exception
{
    public function __construct(Response $response)
    {
        $this->message = sprintf(
            'Ошибка Bytehand: %s %s',
            $response->json('id'),
            $response->json('message'),
        );
    }
}
