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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['room', 'table', 'chair', 'other']);
            $table->enum('status', ['active', 'inactive']);
            $table->string('image')->nullable();
            $table->integer('capacity');
            $table->integer('price');
            $table->index('status', 'status_idx');
            $table->index('type', 'type_idx');
            $table->index(['type', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
