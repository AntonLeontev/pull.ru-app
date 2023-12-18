<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\Domain\Synchronizer\Jobs\CreateOrderFromInsales;
use Src\Domain\Synchronizer\Jobs\CreateProductFromInsales;
use Src\Domain\Synchronizer\Jobs\UpdateProductFromInsales;

class InSalesController extends Controller
{
    public function ordersCreate(Request $request)
    {
        dispatch(new CreateOrderFromInsales($request->all()));
    }

    public function ordersUpdate(Request $request)
    {

    }

    public function productsCreate(Request $request)
    {
        foreach ($request->all() as $product) {
            dispatch(new CreateProductFromInsales($product));
        }
    }

    public function productsUpdate(Request $request)
    {
        foreach ($request->all() as $product) {
            dispatch(new UpdateProductFromInsales($product))->delay(now()->addSeconds(3));
        }
    }
}
