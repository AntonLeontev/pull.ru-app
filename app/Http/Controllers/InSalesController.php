<?php

namespace App\Http\Controllers;

use App\Services\InSales\InSalesApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Domain\Synchronizer\Actions\ResolveDiscount;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Enums\OrderPaymentType;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Jobs\CancelOrderFromInsales;
use Src\Domain\Synchronizer\Jobs\CreateClientInMS;
use Src\Domain\Synchronizer\Jobs\CreateDicardsCard;
use Src\Domain\Synchronizer\Jobs\CreateOrderFromInsales;
use Src\Domain\Synchronizer\Jobs\CreateProductFromInsales;
use Src\Domain\Synchronizer\Jobs\UpdateProductFromInsales;
use Src\Domain\Synchronizer\Models\Client;
use Src\Domain\Synchronizer\Models\Order;

class InSalesController extends Controller
{
    public function ordersCreate(Request $request)
    {
        Order::create([
            'insales_id' => $request->json('id'),
            'payment_status' => OrderPaymentStatus::from($request->json('financial_status')),
            'payment_type' => OrderPaymentType::fromInsales($request->json('payment_gateway_id')),
            'status' => OrderStatus::init,
            'number' => $request->json('number'),
        ]);

        foreach ($request->json('order_lines') as $line) {
            block_product($line['product_id']);
        }
    }

    public function ordersUpdate(Request $request)
    {
        $status = OrderStatus::fromInsales($request->custom_status['permalink']);

        if ($status === OrderStatus::init) {
            Order::where('insales_id', $request->id)
                ->first()
                ->update(['payment_status' => OrderPaymentStatus::from($request->financial_status)]);
        }

        if ($status === OrderStatus::test) {
            dispatch(new CreateOrderFromInsales($request->all()))->onQueue('high');
        }

        if ($status === OrderStatus::approved) {
            dispatch(new CreateOrderFromInsales($request->all()))->onQueue('high');
        }

        if ($status === OrderStatus::cancel) {
            dispatch(new CancelOrderFromInsales($request->all()))->onQueue('high');
        }
    }

    public function productsCreate(Request $request)
    {
        foreach ($request->all() as $product) {
            dispatch(new CreateProductFromInsales($product));
        }
    }

    public function productsUpdate(Request $request)
    {
        foreach ($request->all() as $product) {
            dispatch(new UpdateProductFromInsales($product))->delay(now()->addSeconds(3));
        }
    }

    public function getDiscount(Request $request): JsonResponse
    {
        if (! $request->client['id']) {
            return response()->json([
                'errors' => [
                    'Пользователь не зарегистрирован',
                ],
            ]);
        }

        $client = Client::where('insales_id', $request->client['id'])->first();

        if (is_null($client)) {
            return response()->json([
                'errors' => [
                    'Пользователь не найден',
                ],
            ]);
        }

        return response()->json([
            'discount' => $client->discount_percent,
            'discount_type' => 'PERCENT',
            'title' => "Скидка по карте {$client->discount_card}",
        ]);
    }

    public function clientCreate(Request $request)
    {
        $phone = normalize_phone($request->get('phone'));

        if (! $request->get('registered')) {
            Client::create([
                'insales_id' => $request->get('id'),
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $phone,
            ]);
        }

        if ($request->get('type') !== 'Client::Individual') {
            return;
        }

        if (Client::where('insales_id', $request->get('id'))->exists()) {
            return;
        }

        $client = Client::where('phone', $phone)
            ->where('is_registered', true)
            ->first();

        if (! is_null($client)) {
            return;
        }

        $client = Client::create([
            'insales_id' => $request->get('id'),
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'phone' => $phone,
            'discount_card' => next_discount_card_number(),
            'is_registered' => true,
        ]);

        dispatch(new CreateClientInMS($client))->onQueue('high');
        dispatch(new CreateDicardsCard($client))->onQueue('high');
    }

    public function externalPayment(Request $request, ResolveDiscount $resolveDiscount)
    {
        $order = Order::where('insales_id', $request->get('order_id'))->first();

        if (is_null($order)) {
            return redirect('https://limmite.ru/orders/'.$request->get('key'));
        }

        if ($order->status !== OrderStatus::init) {
            return redirect('https://limmite.ru/orders/'.$request->get('key'));
        }

        if ($order->payment_status !== OrderPaymentStatus::pending) {
            return redirect('https://limmite.ru/orders/'.$request->get('key'));
        }

        $ISOrder = InSalesApi::getOrder($request->get('order_id'))->json();

        $ISOrder = objectize($ISOrder);

        $resolveDiscount->handle($ISOrder);

        $productsIds = collect();

        foreach ($ISOrder->order_lines as $line) {
            $productsIds->push($line->product_id);
        }

        $organizations = [];

        foreach ($productsIds->unique() as $id) {
            $characteristics = collect(InSalesApi::getProduct($id)->json('characteristics'));

            $brand = $characteristics->first(fn ($el) => $el['property_id'] == config('services.inSales.brand_property_id'));

            $organization = organization_by_brand_id($brand['id']);
            $organizations[$id] = $organization;
        }

        return view('pay.cloudpayments', ['order' => $ISOrder, 'organizations' => $organizations]);
    }
}
