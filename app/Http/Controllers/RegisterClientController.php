<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRegisterRequest;
use App\Services\Dicards\DicardsService;
use Src\Domain\Synchronizer\Models\Client;

class RegisterClientController extends Controller
{
    public function show()
    {
        return view('register_form');
    }

    public function create(ClientRegisterRequest $request, DicardsService $service)
    {
        $client = Client::where('phone', $request->get('phone'))->first();

        if (! is_null($client)) {
            $link = $service->getCardLink($client->discount_card);

            return response()->json(['link' => $link]);
        }

        $cardNumber = next_discount_card_number();

        $client = Client::create([...$request->validated(), 'discount_card' => $cardNumber]);
        $service->createCardForClient($client);
        $link = $service->getCardLink($client->discount_card);

        return response()->json(['link' => $link]);
    }
}
