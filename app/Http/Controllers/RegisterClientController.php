<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRegisterForCashierRequest;
use App\Http\Requests\ClientRegisterRequest;
use App\Services\Dicards\DicardsService;
use App\Services\MoySklad\MSApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Src\Domain\Synchronizer\Jobs\CreateClientInInsales;
use Src\Domain\Synchronizer\Jobs\CreateDicardsCard;
use Src\Domain\Synchronizer\Jobs\SubscribeRegisteredFromCashbox;
use Src\Domain\Synchronizer\Jobs\SubscribeRegisteredFromMain;
use Src\Domain\Synchronizer\Models\Client;

class RegisterClientController extends Controller
{
    public function show()
    {
        return view('register_form');
    }

    public function showForCashier()
    {
        return view('register_form_for_cashier');
    }

    public function create(
        ClientRegisterRequest $request,
        DicardsService $dicardsService,
        MSApiService $msService,
    ) {
        $client = Client::where('phone', $request->get('phone'))
            ->where('is_registered', true)
            ->first();

        if (! is_null($client)) {
            $link = $dicardsService->getCardLink($client->discount_card);

            return response()->json(['link' => $link]);
        }

        $cardNumber = next_discount_card_number();

        $client = Client::create([...$request->validated(), 'discount_card' => $cardNumber, 'is_registered' => 1]);

        dispatch(new CreateClientInInsales($client))->onQueue('high');
        dispatch(new SubscribeRegisteredFromCashbox($client))->onQueue('high');

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

    public function createForCashier(
        ClientRegisterForCashierRequest $request,
        MSApiService $msService,
    ) {
        $cardNumber = next_discount_card_number();

        $client = Client::create([...$request->validated(), 'discount_card' => $cardNumber, 'is_registered' => 1]);

        dispatch(new CreateClientInInsales($client))->onQueue('high');
        dispatch(new CreateDicardsCard($client, true))->onQueue('high');
        dispatch(new SubscribeRegisteredFromCashbox($client))->onQueue('high');

        $msClient = $msService->createCounterpartyFromClient($client);
        $client->update(['moy_sklad_id' => $msClient->id]);
    }

    public function registerFromMain(
        Request $request,
        MSApiService $msService,
    ): void {
        $request->merge(['phone' => normalize_phone($request->get('phone'))]);

        $validated = $request->validate([
            'name' => ['required', 'max: 50', 'string'],
            'email' => ['required', 'email:rfc,dns', 'max:100', Rule::unique('clients')->where(fn ($query) => $query->where('is_registered', 1))],
            'phone' => ['required', 'string', 'size:12', 'starts_with:+79', Rule::unique('clients')->where(fn ($query) => $query->where('is_registered', 1))],
        ]);

        $cardNumber = next_discount_card_number();

        $client = Client::create([...$validated, 'discount_card' => $cardNumber, 'is_registered' => 1]);

        dispatch(new CreateClientInInsales($client))->onQueue('high');
        dispatch(new CreateDicardsCard($client))->onQueue('high');
        dispatch(new SubscribeRegisteredFromMain($client))->onQueue('high');

        $msClient = $msService->createCounterpartyFromClient($client);
        $client->update(['moy_sklad_id' => $msClient->id]);
    }
}
