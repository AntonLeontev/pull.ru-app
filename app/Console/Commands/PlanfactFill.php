<?php

namespace App\Console\Commands;

use App\Services\CDEK\CdekApi;
use Illuminate\Console\Command;
use Src\Domain\FinancialAccounting\Jobs\CreateOperationsInAccountingSystem;

class PlanfactFill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:planfact-fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ordersIds = [
            1526274153,
            1529445146,
            1534786935,
            1535229983,
            1536518543,
            1536880628,
            1537288843,
            1537570920,
            1539211861,
            1539796081,
            1540494534,
            1541006592,
            1541221608,
            1541359702,
            1541389432,
            1541684482,
            1541848555,
            1542196492,
            1542739860,
            1543487923,
            1543551134,
            1543851715,
            1543856538,
            1544892780,
            1545685544,
            1545839274,
            1546098595,
            1546103014,
            1546699749,
            1547115854,
            1547278313,
            1547351781,
            1547392570,
            1547683105,
            1548253815,
            1548328622,
            1548352347,
            1549100840,
            1549178681,
            1549221985,
            1549375945,
            1549420501,
            1549932274,
            1550172898,
            1551042921,
            1551625935,
            1552028188,
            1552439565,
            1552439633,
            1553281216,
        ];

        foreach ($ordersIds as $orderId) {
            $response = CdekApi::getOrderByCdekId($orderId);

            dispatch(new CreateOperationsInAccountingSystem($response->json('entity.uuid')));
            $this->info('Заказ '.$orderId.' запущен в очередь');
        }
    }
}
