<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryCalculateRequest;
use App\Services\CDEK\FullfillmentApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Domain\Delivery\DTO\DeliveryTariffDTO;

class DeliveryController extends Controller
{
    public function calculate(DeliveryCalculateRequest $request): JsonResponse
    {
        $tariffs = FullfillmentApi::calculate(
            $request->get('localityId'),
            $request->get('estimatedCost'),
            $request->get('payment'),
            $request->get('weight'),
            $request->get('width'),
            $request->get('height'),
            $request->get('length'),
        )->json();

        return response()->json([
            DeliveryTariffDTO::make(12, 'Tariff 1', 1200, 1, 3, 'Cdek', 'Description to tariff'),
        ]);
    }

    public function locality(Request $request)
    {
        $localities = FullfillmentApi::getLocalities($request->locality)
            ->json('_embedded.localities');

        return response()->json($localities);
    }
}
