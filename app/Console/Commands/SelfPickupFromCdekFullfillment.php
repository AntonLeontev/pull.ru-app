<?php

namespace App\Console\Commands;

use App\Services\CDEK\Entities\Client;
use App\Services\CDEK\Entities\DeliveryRequest;
use App\Services\CDEK\Entities\FullfilmentOrder;
use App\Services\CDEK\Entities\PaymentState;
use App\Services\CDEK\FullfillmentApi;
use Illuminate\Console\Command;

class SelfPickupFromCdekFullfillment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:self-pickup-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает заказ на самозабор из СДЕК';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = FullfillmentApi::getProducts();

        $products = [];
        foreach (range(1, $response->json('page_count')) as $page) {
            FullfillmentApi::getProducts($page)->collect('_embedded.product_offer')
                ->each(function ($product) use (&$products) {

                    // $res = [
                    // 	// 'name' => $product['name'],
                    // 	'productOffer' => $product['id'],
                    // 	'price' => 0,
                    // ];

                    // $item = collect($product['items'])
                    // 	->where('state', 'normal')
                    // 	->first();

                    // if (is_null($item)) {
                    // 	return;
                    // }

                    // $count = $item['count'];
                    // $res['count'] = $count;
                    // $products[] = $res;
                });
        }

        $order = new FullfilmentOrder(
            new Client('Павел Водянкин', 'info@limmite.ru'),
            '+79199502584',
            new PaymentState('paid'),
            $products,
            new DeliveryRequest(48, 0),
            comment: 'САМОЗАБОР',
        );
        // $r = FullfillmentApi::createOrder($order);

        // dd($r->json());
    }
}
