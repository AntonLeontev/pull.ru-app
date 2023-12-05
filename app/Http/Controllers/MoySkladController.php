<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\Domain\Synchronizer\Jobs\UpdateVariantFromMoySklad;

class MoySkladController extends Controller
{
    public function productUpdate(Request $request)
    {

    }

    public function variantUpdate(Request $request)
    {
        dispatch(new UpdateVariantFromMoySklad($request->toArray()));
    }
}
