<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 20)->unique();
            $table->date('payment_date');
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('employee_id')->nullable()->constrained();
            $table->string('subject', 200);
            $table->string('status', 20)->default('draft');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
