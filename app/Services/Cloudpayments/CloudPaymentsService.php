<?php

namespace App\Services\Cloudpayments;

use App\Services\Cloudpayments\Entities\CustomerReceipt;
use App\Services\Cloudpayments\Enums\Type;

class CloudPaymentsService
{
    public function __construct(public CloudPaymentsApi $api)
    {
    }

    public function receipt(CustomerReceipt $customerReceipt, string $invoiceId)
    {
        return $this->api
            ->receipt(
                config('services.cloudpayments.inn'),
                Type::income,
                $customerReceipt,
                $invoiceId,
            )
            ->json();
    }

    public function test(): array
    {
        return $this->api->test()->json();
    }
}
