<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts_sale_works', function (Blueprint $table) {
            $table->id();
            $table->string('processing_ym', 7)->comment('処理年月 YYYY-MM');

            // ── CSV 項目 ──
            $table->string('control_code', 5)->nullable()->comment('col2: コントロールコード');
            $table->string('hinban', 20)->nullable()->comment('col3: 品番(13桁)');
            $table->string('slip_no', 20)->nullable()->comment('col5: 伝票NO');
            $table->decimal('order_qty', 10, 2)->default(0)->comment('col6: 受注数');
            $table->string('order_date_raw', 10)->nullable()->comment('col7: 受注日(生値)');
            $table->date('order_date')->nullable()->comment('col7: 受注日(変換後)');
            $table->decimal('ship_qty', 10, 2)->default(0)->comment('col8: 出荷数');
            $table->string('sale_date_raw', 10)->nullable()->comment('col9: 売上日(生値)');
            $table->date('sale_date')->nullable()->comment('col9: 売上日(変換後)');
            $table->decimal('unit_price', 12, 2)->default(0)->comment('col10: 販売単価');
            $table->string('partner_code', 20)->nullable()->comment('col13: 販売店コード');
            $table->decimal('cost_price', 12, 2)->default(0)->comment('col14: 売上原価');
            $table->string('maintenance_no', 100)->nullable()->comment('col17: 整備注文NO');
            $table->string('red_black_kbn', 2)->default('0')->comment('col18: 赤黒区分(0=黒伝/2=赤伝)');
            $table->string('dispatch_source', 20)->nullable()->comment('col21: 出庫元(倉庫コード)');
            $table->string('staff_code', 20)->nullable()->comment('col22: 担当');
            $table->string('rank_cd', 5)->nullable()->comment('col23: ランク');
            $table->string('item_code', 20)->nullable()->comment('col25: 品目コード');
            $table->string('item_name', 200)->nullable()->comment('col26: 品名');
            $table->string('model_group', 10)->nullable()->comment('col30: 機種グループ');

            // ── 取込時導出項目 ──
            $table->decimal('quantity', 10, 2)->default(0)->comment('最終数量（赤黒区分適用後）');
            $table->string('model_kisyu_cd', 5)->nullable()->comment('品番1-5桁: 機種コード');
            $table->string('vehicle_kisyu_cd', 10)->nullable()->comment('品番6-13桁: 車両コード(XXXXX-YYY形式)');

            // ── 管理項目 ──
            $table->tinyInteger('check_flag')->default(0)->comment('0=正常/1=エラー');
            $table->text('check_message')->nullable()->comment('照合エラーメッセージ');

            $table->timestamps();

            $table->index('processing_ym');
            $table->index(['processing_ym', 'check_flag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts_sale_works');
    }
};
