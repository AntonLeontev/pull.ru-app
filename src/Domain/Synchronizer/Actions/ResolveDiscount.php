<?php

namespace Src\Domain\Synchronizer\Actions;

class ResolveDiscount
{
    public function handle(object $order): void
    {
        if (! $order->discount) {
            return;
        }

        $ids = $order->discount->discount_order_lines_ids ?? [];

        if (empty($ids)) {
            foreach ($order->order_lines as $line) {
                $ids[] = $line->id;
            }
        }

        // В копейках
        $priceWithoutDiscount = 0;
        foreach ($order->order_lines as $line) {
            if (in_array($line->id, $ids)) {
                $priceWithoutDiscount += $line->sale_price * 100 * $line->quantity;
            }
        }

        // *100 это в копейках
        $discount = $order->discount->full_amount * 100;

        $distributedDiscount = 0;

        foreach ($order->order_lines as $line) {
            if (! in_array($line->id, $ids)) {
                continue;
            }

            if (count($ids) === 1) {
                $lineDiscount = $discount - $distributedDiscount;
            } else {
                $lineDiscount = $this->getLineDiscount($line, $priceWithoutDiscount, $discount);
            }

            $distributedDiscount += $lineDiscount;
            $line->line_discount = (string) ($lineDiscount / 100);
            $line->sale_price = (string) ($line->sale_price - round($lineDiscount / $line->quantity) / 100);

            $this->removeByValue($line->id, $ids);
        }
    }

    private function getLineDiscount(object $line, int|float $priceWithoutDiscount, string $discount): int|float
    {
        $partInTotalPrice = ($line->sale_price * 100 * $line->quantity) / $priceWithoutDiscount;

        return round($discount * $partInTotalPrice);
    }

    private function removeByValue(mixed $value, array &$array)
    {
        if (($key = array_search($value, $array)) !== false) {
            unset($array[$key]);
        }
    }
}
