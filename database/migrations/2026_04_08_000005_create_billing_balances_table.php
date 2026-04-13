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
            $table->string('billing_number', 20)->unique()->nullable();
            $table->date('billing_date');
            $table->date('closing_start_date')->nullable();
            $table->foreignId('customer_id')->constrained('customers');
            $table->decimal('prev_amount', 12, 2)->default(0);
            $table->decimal('sales_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('confirmed');
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(['billing_date', 'customer_id'], 'billing_balances_date_customer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_balances');
    }
};
