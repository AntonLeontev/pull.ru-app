<?php

namespace Src\Domain\DiscountSystem\Actions;

use App\Services\Dicards\DicardsService;
use App\Services\MoySklad\MSApiService;
use Src\Domain\DiscountSystem\DiscountSystemService;
use Src\Domain\Synchronizer\Models\Client;

class SetDiscountByPurchasesAction
{
    public function __construct(
        private MSApiService $msApiService,
        private DiscountSystemService $discountService,
        private DicardsService $dicardsService,
    ) {}

    public function handle(Client $client): void
    {
        $sum = $this->msApiService->getPurchasesAmount($client);

        $discountPercent = $this->discountService->percentByPurchases($sum);

        if ($discountPercent !== $client->discount_percent) {
            $this->dicardsService->updateClientDiscount($client, $discountPercent);
        }
    }
}
