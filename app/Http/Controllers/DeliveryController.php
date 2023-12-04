<?php

namespace App\Http\Controllers;

use App\Services\CDEK\FullfillmentApi;
use Illuminate\Http\Request;
use Src\Domain\Delivery\Widget\Widget;

class DeliveryController extends Controller
{
    public function locality(Request $request)
    {
        $localities = FullfillmentApi::getLocalities($request->locality)
            ->json('_embedded.localities');

        return response()->json($localities);
    }

    public function widget()
    {
        $service = new Widget();
        $data = $service->process($_GET, file_get_contents('php://input'));

        return response()->json($data);
    }
}
