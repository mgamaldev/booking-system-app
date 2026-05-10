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
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('type', ['one-on-one', 'recurring' , 'group '])->nullable();
            $table->integer('max_participants')->nullable();
            $table->enum('recurrence_rule' , ['weekly', 'biweekly' , 'monthly'])->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->index('type');
            $table->index('recurrence_rule');
            $table->index('start_date');
            $table->index('end_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('max_participants');
            $table->dropColumn('recurrence_rule');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');

        });
    }
};
