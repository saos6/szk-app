<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_balances', function (Blueprint $table) {
            $table->string('billing_number', 20)->unique()->nullable()->after('id');
            $table->date('closing_start_date')->nullable()->after('billing_date');
            $table->string('status', 20)->default('confirmed')->after('payment_amount');
            $table->decimal('balance_amount', 12, 2)->default(0)->after('status');
            $table->text('remarks')->nullable()->after('balance_amount');
            $table->timestamp('cancelled_at')->nullable()->after('remarks');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('billing_balances', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'billing_number', 'closing_start_date', 'status',
                'balance_amount', 'remarks', 'cancelled_at', 'cancelled_by',
            ]);
        });
    }
};
