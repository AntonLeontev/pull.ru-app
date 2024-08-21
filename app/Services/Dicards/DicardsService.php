<?php

namespace App\Services\Dicards;

use Src\Domain\Synchronizer\Models\Client;

class DicardsService
{
    public function __construct(public DicardsApi $api) {}

    public function createCard(
        string|int $number,
        string $name,
        string $phone,
        ?string $birthday = null,
    ) {
        return $this->api->createCard(
            $number,
            $name,
            $phone,
            $birthday,
        )->json();
    }

    public function createCardForClient(Client $client)
    {
        return $this->api->createCard(
            $client->discount_card,
            $client->name,
            $client->phone,
            $client->birthday,
        )->json();
    }

    public function getCards()
    {
        return $this->api->getCards()->collect();
    }

    public function getCard(int|string $id)
    {
        return $this->api->getCard($id)->json();
    }

    public function getCardLink(int|string $id)
    {
        return $this->api->getCardLink($id)->json('link');
    }

    public function updateCardDiscount(int|string $id, int $discount, bool $push = true)
    {
        $data = [
            'values' => [
                [
                    'label' => 'СКИДКА',
                    'value' => $discount.'%',
                ],
            ],
        ];

        return $this->api->updateCard($id, $data, true, $push);
    }
}
