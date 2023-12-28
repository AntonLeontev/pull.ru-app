<?php

namespace App\Http\Controllers;

class ApiController extends Controller
{
    public function allowedRegions()
    {
        return response()->json(array_keys(config('delivery.cdek.allowed_regions')));
    }
}
