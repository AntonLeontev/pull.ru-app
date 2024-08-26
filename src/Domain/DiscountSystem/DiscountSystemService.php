<?php

namespace Src\Domain\DiscountSystem;

use Illuminate\Support\Collection;

class DiscountSystemService
{
    private Collection $config;

    public function __construct(array $config)
    {
        $this->config = collect($config)->sortByDesc(fn ($el) => $el['purchases_sum']);
    }

    public function percentByPurchases(float|int $purchasesAmount): int
    {
        if ($purchasesAmount < 1) {
            return 0;
        }

        $discount = $this->config
            ->first(fn ($el) => $el['purchases_sum'] <= $purchasesAmount);

        return $discount['discount'];
    }
}
