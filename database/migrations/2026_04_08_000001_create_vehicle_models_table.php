<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->string('model_code', 8);           // 機種コード
            $table->string('color_code', 6);             // 色コード
            $table->string('model_name', 20)->nullable();   // 営業機種記号
            $table->string('model_abbr', 18)->nullable(); // 機種略称
            $table->string('base_model', 10)->nullable();      // 基本機種
            $table->string('model_name_kanji', 32)->nullable(); // 機種名(漢字)
            $table->decimal('purchase_price', 11, 2)->nullable(); // 仕入単価(税抜)
            $table->decimal('selling_price', 11, 2)->nullable(); // 売上単価(税抜)
            $table->decimal('terminal_price', 11, 2)->nullable();        // 末端価格
            $table->decimal('standard_retail_price', 11, 2)->nullable(); // 標準小売価格
            $table->string('g1', 2)->nullable();     // タイプ区分
            $table->string('g2', 2)->nullable();     // 排気量区分
            $table->string('g3', 2)->nullable();
            $table->string('g4', 2)->nullable();
            $table->string('g5', 2)->nullable();
            $table->string('order_number', 8)->nullable(); // オーダーNo
            $table->tinyInteger('tax_type')->nullable(); // 税区分
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};
