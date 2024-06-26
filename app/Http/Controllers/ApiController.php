<?php

namespace App\Http\Controllers;

use App\Http\Requests\RightholdersRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function allowedRegions(): JsonResponse
    {
        $regions = array_keys(config('delivery.cdek.allowed_regions'));
        if ($key = array_search('Татарстан', $regions)) {
            $regions[$key] = 'Республика Татарстан';
        }
        if ($key = array_search('Удмуртия', $regions)) {
            $regions[$key] = 'Республика Удмуртия';
        }
        if ($key = array_search('Марий Эл', $regions)) {
            $regions[$key] = 'Республика Марий Эл';
        }
        if ($key = array_search('Мордовия', $regions)) {
            $regions[$key] = 'Республика Мордовия';
        }

        return response()->json($regions);
    }

    public function organizationsAndBrands(Request $request): JsonResponse
    {
        return response()->json(config('brands'));
    }

    public function rightholders(RightholdersRequest $request): JsonResponse
    {
        return response()->json(['ok' => true, 'request' => $request->all()]);
    }

    public function addititionData()
    {
        $regions = array_keys(config('delivery.cdek.allowed_regions'));
        if ($key = array_search('Татарстан', $regions)) {
            $regions[$key] = 'Республика Татарстан';
        }
        if ($key = array_search('Удмуртия', $regions)) {
            $regions[$key] = 'Республика Удмуртия';
        }
        if ($key = array_search('Марий Эл', $regions)) {
            $regions[$key] = 'Республика Марий Эл';
        }
        if ($key = array_search('Мордовия', $regions)) {
            $regions[$key] = 'Республика Мордовия';
        }

        $brands = config('brands');

        $ip = request()->ip();

        return response()->json([
            'regions' => $regions,
            'brands' => $brands,
            'ip' => $ip,
        ]);
    }
}
