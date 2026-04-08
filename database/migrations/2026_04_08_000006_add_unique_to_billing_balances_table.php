<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_balances', function (Blueprint $table) {
            $table->unique(['billing_date', 'customer_id'], 'billing_balances_date_customer_unique');
        });
    }

    public function down(): void
    {
        Schema::table('billing_balances', function (Blueprint $table) {
            $table->dropUnique('billing_balances_date_customer_unique');
        });
    }
};
