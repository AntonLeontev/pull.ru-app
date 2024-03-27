<?php

namespace App\Services\MoySklad;

use App\Services\MoySklad\Entities\Counterparty;
use App\Services\MoySklad\Entities\Organization;
use App\Services\MoySklad\Entities\Product;
use App\Services\MoySklad\Enums\WebhookAction;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MoySkladApi
{
    public static function getProducts(int $limit = 1000, int $offset = 0): Response
    {
        return Http::moySklad()
            ->get('entity/product', [
                'limit' => $limit,
                'offset' => $offset,
            ]);
    }

    public static function getProduct(string $id): Response
    {
        return Http::moySklad()
            ->get("entity/product/$id");
    }

    public static function updateProduct(string $id, array $params): Response
    {
        return Http::moySklad()
            ->withHeaders([
                'X-Lognex-WebHook-Disable' => '1',
            ])
            ->put("entity/product/$id", $params);
    }

    public static function createProduct(
        string $name,
        array $params = [],
    ): Response {
        $data = [
            'name' => $name,
            ...$params,
        ];

        return Http::moySklad()
            ->post('entity/product', $data);
    }

    public static function createProductFolder(
        string $name,
        array $params = [],
    ): Response {
        $data = [
            'name' => $name,
            ...$params,
        ];

        return Http::moySklad()
            ->post('entity/productfolder', $data);
    }

    public static function updateProductFolder(
        string $id,
        array $params = [],
    ): Response {
        return Http::moySklad()
            ->put("entity/productfolder/$id", $params);
    }

    public static function getProductFolders(): Response
    {
        return Http::moySklad()
            ->get('entity/productfolder');
    }

    public static function priceTypeDefault(): Response
    {
        return Http::moySklad()
            ->get('context/companysettings/pricetype/default');
    }

    public static function getPriceTypes(): Response
    {
        return Http::moySklad()
            ->get('context/companysettings/pricetype');
    }

    public static function getUoms(): Response
    {
        return Http::moySklad()
            ->get('/entity/uom');
    }

    public static function getVariants(string $filter = ''): Response
    {
        return Http::moySklad()
            ->get("/entity/variant?filter=$filter");
    }

    public static function getVariant(string $id): Response
    {
        return Http::moySklad()
            ->get("/entity/variant/$id");
    }

    /**
     * @param  App\Services\MoySklad\Entities\Product  $product
     * @param  App\Services\MoySklad\Entities\Characteristic[]  $characteristics
     */
    public static function createVariant(Product $product, array $characteristics, array $params = []): Response
    {
        return Http::moySklad()
            ->post('entity/variant', [
                'product' => $product,
                'characteristics' => $characteristics,
                ...$params,
            ]);
    }

    /**
     * @param  App\Services\MoySklad\Entities\Characteristic[]  $characteristics
     */
    public static function updateVariant(string $id, array $characteristics, array $params = []): Response
    {
        return Http::moySklad()
            ->withHeaders([
                'X-Lognex-WebHook-Disable' => '1',
            ])
            ->put("entity/variant/$id", [
                'characteristics' => $characteristics,
                ...$params,
            ]);
    }

    public static function getCharacteristics(): Response
    {
        return Http::moySklad()
            ->get('entity/variant/metadata');
    }

    public static function createCharacteristic(string $name): Response
    {
        return Http::moySklad()
            ->post('entity/variant/metadata/characteristics', [
                'name' => $name,
            ]);
    }

    public static function getWebhooks(): Response
    {
        return Http::moySklad()
            ->get('entity/webhook');
    }

    public static function createWebhook(string $url, WebhookAction $action, string $entityType, string $diffType = 'NONE')
    {
        return Http::moySklad()
            ->post('entity/webhook', [
                'url' => $url,
                'action' => $action->value,
                'entityType' => $entityType,
                'diffType' => $diffType,
            ]);
    }

    public static function updateWebhook(string $id, string $url, WebhookAction $action, string $entityType, string $diffType = 'NONE')
    {
        return Http::moySklad()
            ->put("entity/webhook/$id", [
                'url' => $url,
                'action' => $action->value,
                'entityType' => $entityType,
                'diffType' => $diffType,
            ]);
    }

    public static function getOrganizations(): Response
    {
        return Http::moySklad()
            ->get('entity/organization');
    }

    public static function createCustomerOrder(
        Organization $organization,
        Counterparty $counterparty,
        array $data = [],
    ): Response {
        return Http::moySklad()
            ->post('entity/customerorder', [
                'organization' => $organization,
                'agent' => $counterparty,
                ...$data,
            ]);
    }

    public static function updateCustomerOrder(string $id, array $data = []): Response
    {
        return Http::moySklad()
            ->put("entity/customerorder/$id", $data);
    }

    public static function createIndividualCounterparty(
        string $name,
        string $email = null,
        string $phone = null,
    ): Response {
        return Http::moySklad()
            ->post('entity/counterparty', [
                'name' => $name,
                'companyType' => 'individual',
                'email' => $email,
                'phone' => $phone,
            ]);
    }

    public static function getStores(): Response
    {
        return Http::moySklad()
            ->get('entity/store');
    }

    public static function getMoves(int $limit = null, string $expand = null): Response
    {
        $query = 'entity/move?order=created,desc';

        if (! is_null($limit)) {
            $query .= "&limit=$limit";
        }

        if (! is_null($expand)) {
            $query .= "&expand=$expand";
        }

        return Http::moySklad()
            ->get($query);
    }

    public static function getMove(string $id): Response
    {
        return Http::moySklad()
            ->get("entity/move/$id");
    }

    public static function getMovePositions(string $id, int $limit = null, int $offset = null, string $expand = null): Response
    {
        $query = "entity/move/$id/positions?";

        if (! is_null($limit)) {
            $query .= "&limit=$limit";
        }

        if (! is_null($offset)) {
            $query .= "&offset=$offset";
        }

        if (! is_null($expand)) {
            $query .= "&expand=$expand";
        }

        return Http::moySklad()
            ->get($query);
    }

    public static function getOrderStates(): Response
    {
        return Http::moySklad()
            ->get('entity/customerorder/metadata');
    }
}
