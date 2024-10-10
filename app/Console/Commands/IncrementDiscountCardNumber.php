<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IncrementDiscountCardNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:discount-card-increment';

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
        $number = next_discount_card_number();
        $this->info("Последний номер карт теперь $number");
    }
}
