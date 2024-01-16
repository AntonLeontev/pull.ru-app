<?php

namespace Tests\Feature;

use App\Http\Controllers\InSalesController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_with_online_payment(): void
    {
        $data = json_decode(file_get_contents(public_path('../tests/Fixtures/new_online_payment_order.json')), true);
        $response = [
            'Success' => true,
            'ErrorCode' => '0',
            'TerminalKey' => '1705139607309DEMO',
            'Status' => 'NEW',
            'PaymentId' => '3812064454',
            'OrderId' => '3',
            'Amount' => 120,
            'PaymentURL' => 'https://securepayments.tinkoff.ru/sasRsUth',
        ];

        Http::fake(['*' => Http::response($response, 200)]);

        $response = $this->post(action([InSalesController::class, 'ordersCreate']), $data);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'redirect' => 'https://securepayments.tinkoff.ru/sasRsUth',
        ]);

        $this->assertDatabaseHas('orders', [
            'insales_id' => 90431719,
            'payment_status' => 'pending',
            'status' => 'init',
        ]);

        $this->assertDatabaseHas('online_payments', [
            'terminal_key' => '1705139607309DEMO',
            'status' => 'NEW',
            'external_id' => 3812064454,
            'amount' => 120,
            'payment_url' => 'https://securepayments.tinkoff.ru/sasRsUth',
        ]);
    }
}
