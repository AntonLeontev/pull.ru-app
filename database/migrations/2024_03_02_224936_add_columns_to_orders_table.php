<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('fullfillment_id')->after('cdek_id')->nullable();
            $table->unsignedBigInteger('number')->after('id')->nullable();
            $table->unsignedSmallInteger('tries')->after('fullfillment_id')->default(0);
            $table->json('delivery_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['fullfillment_id', 'number', 'tries', 'delivery_info']);
        });
    }
};
