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
        Schema::create('rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('category')->nullable();
            $table->date('end');
            $table->time('endTime')->nullable();
            $table->boolean('isAllDay')->default(false);
            $table->foreignUuid('organizer');
            $table->date('start');
            $table->time('startTime')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rosters');
    }
};
