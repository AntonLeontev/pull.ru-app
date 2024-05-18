<?php

namespace App\Services\Planfact;

use App\Services\Planfact\Entities\Income;
use App\Services\Planfact\Entities\Outcome;
use App\Services\Planfact\Enums\OperationCategoryType;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PlanfactApi
{
    public static function getCompanies(): Response
    {
        return Http::planfact()->get('/api/v1/companies');
    }

    public static function getAccounts(): Response
    {
        return Http::planfact()->get('/api/v1/accounts');
    }

    public static function getOperations(int $accountId = null): Response
    {
        $query = [];

        if ($accountId) {
            $query['filter.accountId'] = config('services.planfact.accounts.cdek');
        }

        return Http::planfact()->get('/api/v1/operations', $query);
    }

    public static function deleteOperation(int $operationId): Response
    {
        return Http::planfact()->delete("/api/v1/operations/{$operationId}");
    }

    public static function getOperationCategories(OperationCategoryType $type = null, string $title = null): Response
    {
        $query = [];

        if ($type) {
            $query['filter.operationCategoryType'] = $type->value;
        }

        if ($title) {
            $query['filter.title'] = $title;
        }

        return Http::planfact()->get('/api/v1/operationcategories', $query);
    }

    public static function getContrAgents(string $title = null): Response
    {
        $query = [];

        if ($title) {
            $query['filter.title'] = $title;
        }

        return Http::planfact()->get('/api/v1/contragents', $query);
    }

    public static function createContrAgent(string $title, string $inn = null, string $kpp = null, string $account = null, string $externalId = null): Response
    {
        return Http::planfact()->post('/api/v1/contragents', [
            'title' => $title,
            'longTitle' => $title,
            'contrAgentInn' => $inn,
            'contrAgentKpp' => $kpp,
            'contrAgentAcct' => $account,
            'contrAgentType' => 'Mixed',
            'externalId' => $externalId,
            'contrAgentGroupId' => config('services.planfact.contragent_group'),
            'rememberCategory' => true,
            'operationIncomeCategoryId' => config('services.planfact.income_category'),
            'operationOutcomeCategoryId' => config('services.planfact.outcome_profit_category'),
        ]);
    }

    public static function updateContrAgent(int $id, string $title = null, string $inn = null, string $kpp = null, string $account = null, string $externalId = null): Response
    {
        return Http::planfact()->put("/api/v1/contragents/{$id}", [
            'title' => $title,
            'longTitle' => $title,
            'contrAgentInn' => $inn,
            'contrAgentKpp' => $kpp,
            'contrAgentAcct' => $account,
            'externalId' => $externalId,
            'contrAgentType' => 'Mixed',
            'contrAgentGroupId' => config('services.planfact.contragent_group'),
            'rememberCategory' => true,
            'operationIncomeCategoryId' => config('services.planfact.income_category'),
            'operationOutcomeCategoryId' => config('services.planfact.outcome_profit_category'),
        ]);
    }

    public static function getContrAgent(int $id): Response
    {
        return Http::planfact()->get("/api/v1/contragents/{$id}");
    }

    public static function createProject(string $title, string $description = null, string $externalID = null): Response
    {
        return Http::planfact()->post('/api/v1/projects', [
            'title' => $title,
            'description' => $description,
            'externalId' => $externalID,
            'closed' => false,
        ]);
    }

    public static function createIncome(Income $income): Response
    {
        return Http::planfact()->post('/api/v1/operations/income', $income);
    }

    public static function createOutcome(Outcome $outcome): Response
    {
        return Http::planfact()->post('/api/v1/operations/outcome', $outcome);
    }

    public static function updateOutcome(int $id, string $date, int $contrAgentId, int $projectId, float $value, string $externalId, int $categoryId, string $comment = null, bool $isCommitted = false): Response
    {
        return Http::planfact()->put("/api/v1/operations/outcome/{$id}", [
            'operationDate' => $date,
            'contrAgentId' => $contrAgentId,
            'accountId' => config('services.planfact.account_id'),
            'comment' => $comment,
            'isCommitted' => $isCommitted,
            'items' => [
                [
                    'calculationDate' => $date,
                    'operationCategoryId' => $categoryId,
                    'contrAgentId' => $contrAgentId,
                    'projectId' => $projectId,
                    'isCalculationCommitted' => $isCommitted,
                    'value' => $value,
                ],
            ],
            'externalId' => $externalId,
        ]);
    }

    public function move(Carbon $debitingDate, Carbon $admissionDate, int $debitingAccountId, int $admissionAccountId, float $amount, string $comment = '', bool $isCommitted = true): Response
    {
        return Http::planfact()->post('/api/v1/operations/move', [
            'debitingDate' => $debitingDate->format('Y-m-d'),
            'admissionDate' => $admissionDate->format('Y-m-d'),
            'debitingAccountId' => $debitingAccountId,
            'admissionAccountId' => $admissionAccountId,
            'debitingItems' => [
                [
                    'calculationDate' => $admissionDate->format('Y-m-d'),
                    'isCalculationCommitted' => $isCommitted,
                    'value' => $amount,
                ],
            ],
            'admissionItems' => [
                [
                    'calculationDate' => $admissionDate->format('Y-m-d'),
                    'isCalculationCommitted' => $isCommitted,
                    'value' => $amount,
                ],
            ],
            'isCommitted' => $isCommitted,
            'comment' => $comment,
        ]);
    }

    public static function deletePayment(int $id): Response
    {
        return Http::planfact()->delete("/api/v1/operations/{$id}");
    }

    public static function getAccountBalance(Carbon $date): Response
    {
        return Http::planfact()->get('/api/v1/businessmetrics/accountbalance', [
            'filter.currentDate' => $date->format('Y-m-d'),
            'filter.accountIds' => config('services.planfact.account_id'),
        ]);
    }

    public static function getCashflow(Carbon $dateStart, Carbon $dateEnd): Response
    {
        return Http::planfact()->get('/api/v1/businessmetrics/cashflow', [
            'filter.periodStartDate' => $dateStart->format('Y-m-d'),
            'filter.periodEndDate' => $dateEnd->format('Y-m-d'),
            'filter.accountId' => config('services.planfact.account_id'),
            'filter.isCalculation' => 'false',
        ]);
    }

    public static function getProjects(): Response
    {
        return Http::planfact()->get('/api/v1/projects');
    }
}
