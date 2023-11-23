<?php

namespace App\Http\Controllers;

use Src\Domain\Synchronizer\Actions\CreateProductFromInsales;
use Src\Domain\Synchronizer\Actions\UpdateProductFromInsales;

class InSalesController extends Controller
{
    public function ordersCreate()
    {

    }

    public function productsCreate(CreateProductFromInsales $createProduct)
    {
        $createProduct->handle();
    }

    public function productsUpdate(UpdateProductFromInsales $updateProduct)
    {
        $updateProduct->handle();
    }
}
