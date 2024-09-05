<?php

namespace App\Http\Controllers;

use App\Services\Unisender\Enums\Gender;
use App\Services\Unisender\UnisenderService;
use Illuminate\Http\Request;

class SubscribtionsController extends Controller
{
    public function subscribeFromFooterForm(Request $request, UnisenderService $uni)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:100'],
            'sex' => ['required', 'in:1,2'],
        ]);

        try {
            $gender = Gender::fromForm($validated['sex']);
            $uni->subscribeFromFooterForm($validated['email'], $gender);
        } catch (\Throwable $th) {
            throw new \Exception('Не удалось добавить в подписку емейл из футера: '.$validated['email']." {$gender->value} . ".$th->getMessage());
        }
    }

    public function subscribeStylistConsultation(Request $request, UnisenderService $uni)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:100'],
            'sex' => ['required', 'in:1,2'],
        ]);

        try {
            $gender = Gender::fromForm($validated['sex']);
            $uni->stylistConsultationSubscribe($validated['email'], $gender);
        } catch (\Throwable $th) {
            throw new \Exception('Не удалось добавить в подписку на стилиста емейл: '.$validated['email']." {$gender->value} . ".$th->getMessage());
        }
    }
}
