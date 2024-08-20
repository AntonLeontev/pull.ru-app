<?php

namespace App\Services\Dicards;

class DicardsService
{
    public function __construct(public DicardsApi $api) {}

    public function createCard(
        string|int $number,
        string $name,
        string $phone,
        string $birthday,
    ) {
        return $this->api->createCard(
            $number,
            $name,
            $phone,
            $birthday,
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
}
