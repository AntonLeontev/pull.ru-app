<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\Domain\DiscountSystem\Jobs\ScheduleDiscountUpdatingFromRetailDemand;
use Src\Domain\DiscountSystem\Jobs\ScheduleDiscountUpdatingFromRetailReturn;
use Src\Domain\Synchronizer\Jobs\CreateCounterPartyFromMoySklad;
use Src\Domain\Synchronizer\Jobs\UpdateProductFromMoySklad;
use Src\Domain\Synchronizer\Jobs\UpdateVariantFromMoySklad;

class MoySkladController extends Controller
{
    public function productUpdate(Request $request)
    {
        dispatch(new UpdateProductFromMoySklad($request->toArray()));
    }

    public function variantUpdate(Request $request)
    {
        dispatch(new UpdateVariantFromMoySklad($request->toArray()));
    }

    public function counterpartyCreate(Request $request)
    {
        dispatch(new CreateCounterPartyFromMoySklad($request->toArray()));
    }

    public function retaildemandCreate(Request $request)
    {
        foreach ($request->events as $event) {
            $retailDemandId = str(data_get($event, 'meta.href'))->afterLast('/')->value();

            dispatch(new ScheduleDiscountUpdatingFromRetailDemand($retailDemandId));
        }
    }

    public function retailsalesreturnCreate(Request $request)
    {
        foreach ($request->events as $event) {
            $retailSalesReturnId = str(data_get($event, 'meta.href'))->afterLast('/')->value();

            dispatch(new ScheduleDiscountUpdatingFromRetailReturn($retailSalesReturnId));
        }
    }
}
