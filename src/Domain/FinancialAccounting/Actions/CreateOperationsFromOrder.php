<?php

namespace Src\Domain\FinancialAccounting\Actions;

use App\Services\MoySklad\MoySkladApi;
use App\Services\Planfact\Entities\Income;
use App\Services\Planfact\Entities\Outcome;
use App\Services\Planfact\PlanfactService;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Collection;
use Src\Domain\FinancialAccounting\DTO\CdekOrderDTO;
use Src\Domain\Synchronizer\Models\Product;
use Src\Domain\Synchronizer\Models\Variant;

class CreateOperationsFromOrder
{
    public function __construct(
        public PlanfactService $planfactService,
        public MoySkladApi $moySkladApi,
    ) {
    }

    public function handle(CdekOrderDTO $dto): void
    {
        // Доходы по заказу
        if ($dto->transactedPayment) {
            $this->handleIncomes($dto);
        }

        // Расходы по заказу
        $outcome = $this->createOutcome($dto);
        $this->planfactService->createOutcome($outcome);
    }

    private function handleIncomes(CdekOrderDTO $dto): void
    {
        $incomes = collect();

        collect($dto->items)
            ->filter(static fn ($item) => $item['delivery_amount'] > 0)
            ->each($this->createIncomeForProduct($incomes, $dto));

        if ($dto->recipientDeliveryCost > 0) {
            $incomes->push($this->createIncomeForDelivery($dto));
        }

        foreach ($incomes as $income) {
            $this->planfactService->createIncome($income);
        }
    }

    private function getOperationCategoryId(Product $product): int
    {
        $path = $this->moySkladApi->getProduct($product->moy_sklad_id)->json('pathName');

        if (str_contains($path, '/Мужчинам/')) {
            return config('services.planfact.operation_categories.income.men');
        }

        return config('services.planfact.operation_categories.income.women');
    }

    private function createIncomeForDelivery(CdekOrderDTO $dto): Income
    {
        return new Income(
            Carbon::parse($dto->deliveryDate),
            $dto->recipientDeliveryCost,
            config('services.planfact.accounts.cdek'),
            true,
            true,
            operationCategoryId: config('services.planfact.operation_categories.income.delivery'),
            projectId: config('services.planfact.projects.limmite'),
            comment: 'Заказ '.$dto->number,
        );
    }

    private function createIncomeForProduct(Collection $incomes, CdekOrderDTO $dto): Closure
    {
        return function ($item) use ($incomes, $dto) {
            $product = Variant::find($item['ware_key'])->product;

            $operationCategoryId = $this->getOperationCategoryId($product);
            $price = $item['payment']['value'];

            $incomes->push(new Income(
                Carbon::parse($dto->deliveryDate),
                $price,
                config('services.planfact.accounts.cdek'),
                true,
                true,
                operationCategoryId: $operationCategoryId,
                projectId: config('services.planfact.projects.limmite'),
                comment: 'Заказ '.$dto->number,
            ));
        };
    }

    private function createOutcome(CdekOrderDTO $dto): Outcome
    {
        $categoryId = $dto->isReturn
            ? config('services.planfact.operation_categories.outcome.delivery_return_orders')
            : config('services.planfact.operation_categories.outcome.delivery_direct_orders');

        return new Outcome(
            Carbon::parse($dto->deliveryDate),
            $dto->totalExpence,
            config('services.planfact.accounts.cdek'),
            true,
            true,
            operationCategoryId: $categoryId,
            projectId: config('services.planfact.projects.limmite'),
            comment: 'Заказ '.$dto->number,
        );
    }
}
