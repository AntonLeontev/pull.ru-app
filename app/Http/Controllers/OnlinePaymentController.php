<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\Domain\Payments\Models\OnlinePayment;

class OnlinePaymentController extends Controller
{
    public function cloudpaymentsPay(Request $request)
    {
        OnlinePayment::create([
            'status' => $request->Status,
            'transaction_id' => $request->TransactionId,
            'payment_amount' => $request->PaymentAmount,
            'user_email' => $request->Email,
        ]);

        return response()->json(['code' => 0]);
    }
}
