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
        Schema::create('time_intervals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('timer_id');
            $table->foreign('timer_id')->references('id')->on('timers')->onDelete('cascade');

            $table->dateTime('start');

            $table->dateTime('stop')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_intervals');
    }
};
