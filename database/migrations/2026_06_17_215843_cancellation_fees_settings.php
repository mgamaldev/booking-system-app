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
        Schema::create('cancellation_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('min_hours_before_slot')->default(0);
            $table->unsignedInteger('max_hours_before_slot')->nullable();
            $table->enum('fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancellation_fee_settings');
    }
};
