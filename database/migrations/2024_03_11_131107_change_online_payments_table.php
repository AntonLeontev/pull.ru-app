<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Src\Domain\Synchronizer\Models\Order;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('online_payments', function (Blueprint $table) {
            $table->dropColumn('terminal_key');
            $table->renameColumn('external_id', 'transaction_id');
            $table->dropColumn('amount');
            $table->renameColumn('payment_url', 'payment_amount');
            $table->string('user_email')->nullable()->after('order_id');
            $table->foreignIdFor(Order::class)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
