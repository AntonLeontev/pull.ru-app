<?php

namespace App\Http\Controllers;

use App\Services\InSales\InSalesApi;
use App\Services\Tinkoff\TinkoffService;
use Illuminate\Http\Request;
use Src\Domain\Synchronizer\Actions\ResolveDiscount;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Enums\OrderPaymentType;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Jobs\CreateOrderFromInsales;
use Src\Domain\Synchronizer\Jobs\CreateProductFromInsales;
use Src\Domain\Synchronizer\Jobs\UpdateProductFromInsales;
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

        if ($status === OrderStatus::approved) {
            dispatch(new CreateOrderFromInsales($request->all()));
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

    public function externalPayment(Request $request, TinkoffService $tinkoffService, ResolveDiscount $resolveDiscount)
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

        foreach ($$ISOrder->order_lines as $line) {
            $productsIds->push($line->product_id);
        }

        $organizations = [];

        foreach ($productsIds->unique() as $id) {
            $characteristics = collect(InSalesApi::getProduct($id)->json('characteristics'));

            $brand = $characteristics->first(fn ($el) => $el['property_id'] == config('services.inSales.brand_property_id'));

            $organization = organization_by_brand_id($brand['id']);
            $organizations[$id] = $organization;
        }

        // dd($organizations);
        return view('pay.cloudpayments', ['order' => $ISOrder, 'organizations' => $organizations]);
    }
}
