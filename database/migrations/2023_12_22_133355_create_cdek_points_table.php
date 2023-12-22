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
        Schema::create('cdek_points', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name')->nullable();
            $table->string('uuid')->unique();
            $table->string('work_time')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('owner_code', 100)->nullable();
            $table->boolean('take_only')->nullable();
            $table->boolean('is_handout')->nullable();
            $table->boolean('is_reception')->nullable();
            $table->boolean('is_dressing_room')->nullable();
            $table->boolean('is_ltl')->nullable();
            $table->boolean('have_cashless')->nullable();
            $table->boolean('have_cash')->nullable();
            $table->boolean('allowed_cod')->nullable();
            $table->unsignedDecimal('weight_min', 8, 1)->nullable();
            $table->unsignedDecimal('weight_max', 8, 1)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->unsignedMediumInteger('region_code')->nullable()->index();
            $table->string('region')->nullable();
            $table->unsignedMediumInteger('city_code')->nullable();
            $table->string('city')->nullable();
            $table->string('fias_guid', 100)->nullable();
            $table->string('postal_code', 100)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->string('address')->nullable();
            $table->string('address_full')->nullable();
            $table->boolean('fulfillment')->nullable();
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cdek_points');
    }
};
