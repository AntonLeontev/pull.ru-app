<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\InSales\InSalesApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Models\Product;
use Src\Domain\Synchronizer\Models\Variant;

class AddQuantityToInsales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $products)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $import = ['variants' => []];
        $warehouses = collect(config('sync.warehouses_match'));
        $cdekWarehouse = $this->products[0]['_embedded']['warehouse']['id'];
        $insalesWarehouse = $warehouses->first(fn ($warehouse) => $warehouse['cdekff'] === $cdekWarehouse)['insales'];

        foreach ($this->products as $product) {
            $id = (int) $product['_embedded']['productOffer']['extId'];
            [$productId, $variantId] = $this->getInsalesIds($id);
            $quantityInInsales = $this->getQuantityInInsales($productId, $variantId, $insalesWarehouse);

            $insalesVariant = [
                'id' => $variantId,
                'quantity_at_warehouse'.$insalesWarehouse => $product['quantityPlace'] + $quantityInInsales,
            ];

            $import['variants'][] = $insalesVariant;
        }

        $response = InSalesApi::updateVariantsGroup($import)->json();
    }

    private function getInsalesIds(int $id): array
    {
        $variant = Variant::find($id);
        $product = Product::find($variant->product_id);

        return [$product->insales_id, $variant->insales_id];
    }

    private function getQuantityInInsales(int $productId, int $variantId, int $warehouse): int
    {
        return (int) InSalesApi::getVariant($productId, $variantId)->json("quantity_at_warehouse$warehouse");
    }
}
