<?php

namespace App\Http\Controllers;

use App\Services\InSales\InSalesApi;
use App\Services\Tinkoff\TinkoffService;
use Illuminate\Http\Request;
use Src\Domain\Payments\Enums\OnlinePaymentStatus;
use Src\Domain\Payments\Models\OnlinePayment;
use Src\Domain\Synchronizer\Actions\ResolveDiscount;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Jobs\CreateOrderFromInsales;
use Src\Domain\Synchronizer\Jobs\CreateProductFromInsales;
use Src\Domain\Synchronizer\Jobs\UpdateProductFromInsales;
use Src\Domain\Synchronizer\Models\Order;

class InSalesController extends Controller
{
    public function ordersCreate(Request $request, TinkoffService $tinkoffService, ResolveDiscount $resolveDiscount)
    {
        if ($request->get('payment_gateway_id') != config('services.inSales.online_payment_gateway_id')) {
            dispatch(new CreateOrderFromInsales($request->all()));

            return;
        }

        $request = objectize($request->all());

        $resolveDiscount->handle($request);

        $order = Order::create([
            'insales_id' => $request->id,
            'payment_status' => OrderPaymentStatus::pending,
            'status' => OrderStatus::init,
        ]);

        $response = $tinkoffService->init($request);

        OnlinePayment::create([
            'order_id' => $order->id,
            'terminal_key' => $response->json('TerminalKey'),
            'status' => OnlinePaymentStatus::from($response->json('Status')),
            'external_id' => $response->json('PaymentId'),
            'amount' => $response->json('Amount'),
            'payment_url' => $response->json('PaymentURL'),
        ]);

        return response()->json(['ok' => true, 'redirect' => $response->json('PaymentURL')]);
    }

    public function ordersUpdate(Request $request)
    {

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

    public function externalPayment(Request $request, TinkoffService $tinkoffService, ResolveDiscount $resolveDiscount)
    {
        $order = Order::where('insales_id', $request->get('order_id'))->first();

        if (is_null($order)) {
            return redirect('https://pull.ru/orders/'.$request->get('key'));
        }

        if ($order->status !== OrderStatus::init) {
            return redirect('https://pull.ru/orders/'.$request->get('key'));
        }

        if ($order->payment_status !== OrderPaymentStatus::pending) {
            return redirect('https://pull.ru/orders/'.$request->get('key'));
        }

        $ISOrder = InSalesApi::getOrder($request->get('order_id'))->json();

        $ISOrder = objectize($ISOrder);

        $resolveDiscount->handle($ISOrder);

        $response = $tinkoffService->init($ISOrder);

        OnlinePayment::create([
            'order_id' => $order->id,
            'terminal_key' => $response->json('TerminalKey'),
            'status' => OnlinePaymentStatus::from($response->json('Status')),
            'external_id' => $response->json('PaymentId'),
            'amount' => $response->json('Amount'),
            'payment_url' => $response->json('PaymentURL'),
        ]);

        return redirect($response->json('PaymentURL'));
    }
}
