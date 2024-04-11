<?php

namespace App\Http\Controllers;

use Src\Domain\Delivery\Widget\Widget;

class DeliveryController extends Controller
{
    public function widget()
    {
        $service = new Widget();
        $data = $service->process($_GET, file_get_contents('php://input'));

        return response()->json($data);
    }

    public function orderStatus()
    {

    }
}
