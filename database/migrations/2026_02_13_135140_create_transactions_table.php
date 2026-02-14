<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique();
            $table->string('type'); // deposit, buy, sell, fee
            $table->string('asset')->nullable(); // BTC, ETH, USDT, NGN
            $table->decimal('amount', 20, 8);
            $table->decimal('fee', 20, 8)->default(0);
            $table->decimal('rate', 20, 8)->nullable(); // Exchange rate at time of transaction
            $table->string('status')->default('completed');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type', 'asset']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};