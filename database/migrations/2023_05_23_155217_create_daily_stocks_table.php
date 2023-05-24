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
        Schema::create('daily_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('closed_by')->constrained('users');
            $table->foreignId('inventory_id');
            $table->integer('before');
            $table->integer('restock')->nullable();
            $table->integer('sold');
            $table->integer('damaged')->nullable();
            $table->integer('after');
            $table->float('cash')->nullable();
            $table->float('online')->nullable();
            $table->float('profit');
            $table->timestamps();
            // kena simpan duit float masa opening, net sales, gross sales
            // maybe kena ada opened_at = record waktu opening. created_at = record waktu closing
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stocks');
    }
};
