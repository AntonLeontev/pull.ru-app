<?php

namespace App\Services\CDEK\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class CdekApiException extends Exception
{
    public function __construct(Response $response)
    {
        $this->message = sprintf(
            '[%s] request to [%s] finished with status %s %s: type - %s, %s',
            $response->transferStats->getRequest()->getMethod(),
            $response->transferStats->getRequest()->getUri(),
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $response->json('requests.0.type') ?? $response->json('error') ?? $response->json('errors.0.message'),
            $response->json('requests.0.errors.0.message') ?? $response->json('error_description'),
        );
    }
}
