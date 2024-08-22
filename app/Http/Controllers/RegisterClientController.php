<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRegisterRequest;
use App\Services\Dicards\DicardsService;
use App\Services\MoySklad\MSApiService;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Jobs\CreateClientInInsales;
use Src\Domain\Synchronizer\Models\Client;

class RegisterClientController extends Controller
{
    public function show()
    {
        return view('register_form');
    }

    public function create(
        ClientRegisterRequest $request,
        DicardsService $dicardsService,
        MSApiService $msService,
    ) {
        $client = Client::where('phone', $request->get('phone'))->first();

        if (! is_null($client)) {
            $link = $dicardsService->getCardLink($client->discount_card);

            return response()->json(['link' => $link]);
        }

        $cardNumber = next_discount_card_number();

        $client = Client::create([...$request->validated(), 'discount_card' => $cardNumber]);

        dispatch(new CreateClientInInsales($client))->onQueue('high');

        $msClient = $msService->createCounterpartyFromClient($client);
        $client->update(['moy_sklad_id' => $msClient->id]);

        try {
            $dicardsService->createCardForClient($client);
            $link = $dicardsService->getCardLink($client->discount_card);
        } catch (\Throwable $th) {
            Log::channel('telegram')->alert('Не удалось выдать скидочную карту новому пользователю: '.$th->getMessage(), [$client]);
        }

        if (! empty($link)) {
            return response()->json(['link' => $link]);
        }

        return response()->json(['ok' => true]);
    }
}
