<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->string('stock_ym', 7);            // 年月 YYYY-MM
            $table->string('warehouse_code', 20);     // 倉庫コード
            $table->string('vehicle_model_code', 8);  // 機種コード
            $table->string('frame_no', 10);           // フレームNo
            $table->integer('prev_stock')->default(0); // 前月繰越在庫数
            $table->integer('in_stock')->default(0);   // 当月入庫数
            $table->integer('out_stock')->default(0);  // 当月出庫数
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(
                ['stock_ym', 'warehouse_code', 'vehicle_model_code', 'frame_no'],
                'inventory_balances_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};
