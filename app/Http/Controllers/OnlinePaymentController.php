<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\Domain\Payments\Jobs\BindPaymentToOrder;

class OnlinePaymentController extends Controller
{
    public function cloudpaymentsPay(Request $request)
    {
        dispatch(new BindPaymentToOrder($request->all()))->onQueue('high');

        return response()->json(['code' => 0]);
    }
}
