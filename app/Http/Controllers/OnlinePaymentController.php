<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnlinePaymentController extends Controller
{
    public function tinkoff(Request $request)
    {
        return 'OK';
    }

    public function tinkoffSuccess(Request $request)
    {

    }

    public function tinkoffFail(Request $request)
    {

    }
}
