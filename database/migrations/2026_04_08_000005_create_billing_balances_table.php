<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_balances', function (Blueprint $table) {
            $table->id();
            $table->date('billing_date');
            $table->foreignId('customer_id')->constrained('customers');
            $table->decimal('prev_amount', 12, 2)->default(0);
            $table->decimal('sales_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_balances');
    }
};
