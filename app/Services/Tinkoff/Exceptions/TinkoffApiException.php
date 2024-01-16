<?php

namespace App\Services\Tinkoff\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class TinkoffApiException extends Exception
{
    public function __construct(Response $response)
    {
        $this->message = sprintf(
            '[%s] request to [%s] finished with error: %s, код: %s; %s',
            $response->transferStats->getRequest()->getMethod(),
            $response->transferStats->getRequest()->getUri(),
            $response->json('Message'),
            $response->json('ErrorCode'),
            $response->json('Details'),
        );
    }
}
