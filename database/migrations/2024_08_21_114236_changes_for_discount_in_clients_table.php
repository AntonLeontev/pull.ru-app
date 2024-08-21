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
        Schema::table('clients', function (Blueprint $table) {
            $table->after('email', function ($table) {
                $table->string('discount_card')->nullable();
                $table->unsignedTinyInteger('discount_percent')->default(0);
                $table->date('discount_reset_date')->nullable();
            });

            $table->dropColumn('is_registered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['discount_card', 'discount_percent', 'discount_reset_date']);

            $table->boolean('is_registered')->default(0)->nullable()->after('email');
        });
    }
};
