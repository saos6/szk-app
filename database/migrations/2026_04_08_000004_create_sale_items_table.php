<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('line_no');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model_code', 8)->nullable();         // 機種コード
            $table->string('frame_number', 30)->nullable();        // フレームNo
            $table->string('warehouse_code', 20)->nullable();  // 倉庫コード
            $table->string('color_code', 6)->nullable();           // 色コード
            $table->string('model_name', 200)->nullable();       // 機種名
            $table->decimal('quantity', 8, 2)->default(1);    // 数量
            $table->string('unit', 10)->default('台');         // 単位
            $table->decimal('purchase_price', 12, 2)->default(0);    // 仕入単価
            $table->decimal('selling_price', 12, 2)->default(0);    // 売上単価
            $table->decimal('terminal_price', 12, 2)->nullable(); // 末端価格
            $table->string('tax_rate', 4)->default('10');      // 税率
            $table->decimal('sale_amount', 12, 2)->default(0); // 売上金額
            $table->decimal('cogs_amount', 12, 2)->default(0); // 仕入金額
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
