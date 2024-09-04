<?php

namespace App\Console\Commands;

use App\Models\TelegramLog;
use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;

class LogsToTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:logs-to-telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log gathered logs to telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = '';

        $logs = TelegramLog::query()
            ->take(30)
            ->get();

        if ($logs->isEmpty()) {
            return;
        }

        $logs->each(static function ($log) use ($message) {
            $message .= $log->text.' '.$log->created_at->format('H:i:s');
        });

        TelegramService::log($message);

        TelegramLog::whereIn('id', $logs->pluck('id'))->delete();
    }
}
