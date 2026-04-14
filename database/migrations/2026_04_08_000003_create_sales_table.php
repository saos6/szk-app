<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number', 20)->unique();       // 売上番号
            $table->string('import_no', 20)->nullable()->index()->comment('部品売上取込 伝票NO（重複チェック用）');
            $table->string('partner_slip_no', 50)->nullable()->comment('相手伝票NO');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->date('sale_date');                         // 売上日
            $table->date('order_date')->nullable()->comment('受注日');
            $table->date('delivery_date')->nullable();         // 納品日
            $table->string('subject', 200);                   // 件名
            $table->string('status', 20)->default('draft');   // ステータス
            $table->string('sale_type', 20)->nullable()->comment('売上区分: vehicle=車両, parts=部品');
            $table->decimal('subtotal', 12, 2)->default(0);   // 売上小計（税抜）
            $table->decimal('tax_amount', 12, 2)->default(0); // 消費税
            $table->decimal('total_amount', 12, 2)->default(0); // 税込合計
            $table->decimal('cogs_total', 12, 2)->default(0); // 仕入合計
            $table->text('remarks')->nullable();
            $table->boolean('is_deleted')->default(false)->index();
            $table->foreignId('billing_balance_id')->nullable()->constrained('billing_balances')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
