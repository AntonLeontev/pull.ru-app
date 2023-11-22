<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\Domain\Synchronizer\Actions\CreateProductFromInsales;

class InSalesController extends Controller
{
    public function ordersCreate()
    {

    }

    public function productsCreate(CreateProductFromInsales $createProduct)
    {
        $createProduct->handle();
    }

    public function productsUpdate(Request $request)
    {

    }
}
