<?php

namespace Src\Domain\FinancialAccounting\Imports;

use App\Services\Planfact\Entities\Outcome;
use App\Services\Planfact\PlanfactService;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Src\Domain\FinancialAccounting\Exceptions\CdekOperationsImportException;
use Src\Domain\FinancialAccounting\Models\CdekTransaction;

class CdekExpendsImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    public function __construct(public PlanfactService $planfactService)
    {
    }

    public function collection(Collection $rows)
    {
        $skipped = 0;
        $processed = 0;
        $errors = collect();

        foreach ($rows as $key => $row) {
            try {
                if (CdekTransaction::where('ext_id', (int) $row->get('id'))->exists()) {
                    $skipped++;

                    continue;
                }

                CdekTransaction::create(['ext_id' => (int) $row->get('id')]);

                $outcome = $this->createOutcome($row);

                $this->planfactService->createOutcome($outcome);
                $processed++;
            } catch (\Exception $e) {
                $line = $key + 2;
                $errors->push('Строка '.$line.', ID '.$row->get('id').', '.$e->getMessage());

                continue;
            }
        }

        $message = "Завершен импорт расходов СДЭК.\nИмпортировано $processed записей.\nПропущено $skipped дублей.";

        if ($errors->isNotEmpty()) {
            $message .= "\n\nОшибки:\n\n".$errors->join("\n");
        }

        TelegramService::notification($message);
    }

    public function chunkSize(): int
    {
        return 100;
    }

    private function getOperationCategoryId(Collection $row): int
    {
        if ($row->get('entityType') === 'Orderadmin\Storage\Entity\Movement\Acceptance') {
            return config('services.planfact.operation_categories.outcome.acceptance');
        }

        if ($row->get('entityType') === 'Orderadmin\Storage\Entity\Warehouse') {
            if (str($row->get('comment'))->contains('Стоимость страховки', true)) {
                return config('services.planfact.operation_categories.outcome.warehouse_insurance');
            }

            if (str($row->get('comment'))->contains('плата за хранение', true)) {
                return config('services.planfact.operation_categories.outcome.storage');
            }
        }

        if ($row->get('entityType') === 'Orderadmin\Products\Entity\Order') {
            if (str($row->get('comment'))->contains('Возврат', true)) {
                return config('services.planfact.operation_categories.outcome.return');
            }

            if (str($row->get('comment'))->contains('плата за отгрузку', true)) {
                return config('services.planfact.operation_categories.outcome.assembly');
            }
        }

        throw new CdekOperationsImportException('Не удалось подобрать статью расходов', 1);
    }

    private function createOutcome(Collection $row): Outcome
    {
        $operationCategoryId = $this->getOperationCategoryId($row);

        return new Outcome(
            Carbon::parse($row->get('transactionDate')),
            str($row->get('value'))->replace('-', '')->toFloat(),
            config('services.planfact.accounts.cdek'),
            true,
            true,
            null,
            config('services.planfact.projects.limmite'),
            $operationCategoryId,
            comment: $row->get('comment'),
        );
    }
}
