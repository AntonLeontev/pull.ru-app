<?php

namespace App\Services\Planfact\Exceptions;

use Illuminate\Http\Client\Response;

class PlanfactValidationException extends PlanfactException
{
    public function __construct(Response $response)
    {
        $this->message = sprintf(
            'Planfact request failed. Сообщение: %s',
            $response->json('errorMessage')
        );
    }
}
