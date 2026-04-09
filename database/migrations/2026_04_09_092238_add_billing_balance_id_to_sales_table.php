<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('billing_balance_id')
                ->nullable()
                ->constrained('billing_balances')
                ->nullOnDelete()
                ->after('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['billing_balance_id']);
            $table->dropColumn('billing_balance_id');
        });
    }
};
