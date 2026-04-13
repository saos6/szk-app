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
            $table->string('kisyu_cd', 8);           // 機種コード
            $table->string('iro_cd', 6);             // 色コード
            $table->string('kisyu_nm', 20)->nullable();   // 営業機種記号
            $table->string('kisyu_nm_r', 18)->nullable(); // 機種略称
            $table->string('kihon', 10)->nullable();      // 基本機種
            $table->string('kisyu_nm_h', 32)->nullable(); // 機種名(漢字)
            $table->decimal('sre_tan', 11, 2)->nullable(); // 仕入単価(税抜)
            $table->decimal('uri_tan', 11, 2)->nullable(); // 売上単価(税抜)
            $table->decimal('terminal_price', 11, 2)->nullable();        // 末端価格
            $table->decimal('standard_retail_price', 11, 2)->nullable(); // 標準小売価格
            $table->string('g1', 2)->nullable();     // タイプ区分
            $table->string('g2', 2)->nullable();     // 排気量区分
            $table->string('g3', 2)->nullable();
            $table->string('g4', 2)->nullable();
            $table->string('g5', 2)->nullable();
            $table->string('order_no', 8)->nullable(); // オーダーNo
            $table->tinyInteger('zei_kbn')->nullable(); // 税区分
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};
