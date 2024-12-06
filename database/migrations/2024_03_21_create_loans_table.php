<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('interest_rate'); // Annual interest rate (whole number)
            $table->integer('term_months');
            $table->enum('status', ['pending', 'active', 'completed', 'defaulted'])->default('pending');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedInteger('monthly_payment')->nullable();
            $table->unsignedInteger('remaining_balance')->nullable();
            $table->timestamp('next_payment_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
