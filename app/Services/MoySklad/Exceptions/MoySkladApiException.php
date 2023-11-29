<?php

namespace App\Services\MoySklad\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class MoySkladApiException extends Exception
{
    public function __construct(Response $response)
    {
        $this->message = sprintf(
            '[%s] request to [%s] finished with status %s %s: %s, код: %s; %s',
            $response->transferStats->getRequest()->getMethod(),
            $response->transferStats->getRequest()->getUri(),
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $response->json('errors.0.error'),
            $response->json('errors.0.code'),
            $response->json('errors.0.moreInfo'),
        );
    }
}
