<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\Domain\Synchronizer\Jobs\CreateProductFromInsales;
use Src\Domain\Synchronizer\Jobs\UpdateProductFromInsales;

class InSalesController extends Controller
{
    public function ordersCreate(Request $request)
    {

    }

    public function ordersUpdate(Request $request)
    {

    }

    public function productsCreate(Request $request)
    {
        dispatch(new CreateProductFromInsales($request->all()));
    }

    public function productsUpdate(Request $request)
    {
        dispatch(new UpdateProductFromInsales($request->all()))->delay(now()->addSeconds(3));
    }
}
