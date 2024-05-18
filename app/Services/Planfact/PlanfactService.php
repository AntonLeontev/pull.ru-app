<?php

namespace App\Services\Planfact;

use App\Services\Planfact\Exceptions\PlanfactValidationException;
use Illuminate\Http\Client\Response;

/**
 * @method Response createIncome(App\Services\Planfact\Entities\Income $income)
 * @method Response createOutcome(App\Services\Planfact\Entities\Outcome $outcome)
 */
class PlanfactService
{
    public function __construct(public PlanfactApi $api)
    {
    }

    public function __call(string $name, array $arguments): Response
    {
        $response = $this->api->{$name}(...$arguments);

        if (! $response->json('isSuccess')) {
            throw new PlanfactValidationException($response);
        }

        return $response;
    }
}
