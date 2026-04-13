<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->date('order_date')->nullable()->comment('受注日')->after('sale_date');
            $table->string('partner_slip_no', 50)->nullable()->comment('相手伝票NO')->after('import_no');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['order_date', 'partner_slip_no']);
        });
    }
};
