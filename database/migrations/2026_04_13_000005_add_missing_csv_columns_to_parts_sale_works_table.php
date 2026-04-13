<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts_sale_works', function (Blueprint $table) {
            // CSV全項目対応（不足カラム追加）
            $table->string('monthly_f_kbn', 5)->nullable()->comment('col1: 月報Ｆ登録区分')->after('processing_ym');
            $table->string('office_code', 10)->nullable()->comment('col4: 営業所ｺｰﾄﾞ')->after('hinban');
            $table->string('sale_kbn', 5)->nullable()->comment('col11: 販売区分')->after('unit_price');
            $table->string('les_rate', 10)->nullable()->comment('col12: ﾚｽ率')->after('sale_kbn');
            $table->string('terminal_price', 20)->nullable()->comment('col15: 末端価格')->after('cost_price');
            $table->string('breakdown_code', 10)->nullable()->comment('col16: 内訳ｺｰﾄﾞ')->after('terminal_price');
            $table->string('invoice_kbn', 5)->nullable()->comment('col19: 請求書発行区分')->after('red_black_kbn');
            $table->string('invoice_m_kbn', 5)->nullable()->comment('col20: 請求書Ｍ登録区分')->after('invoice_kbn');
            $table->string('first_ship_kbn', 5)->nullable()->comment('col24: 初回出荷区分')->after('rank_cd');
            $table->string('open_kbn', 5)->nullable()->comment('col27: オープン区分')->after('item_name');
            $table->string('dealer_code', 20)->nullable()->comment('col28: 販売店コード（全角）')->after('open_kbn');
            $table->string('standard_retail_price', 20)->nullable()->comment('col29: 標準小売価格')->after('dealer_code');
            $table->string('filler', 100)->nullable()->comment('col31: FILLER')->after('model_group');
        });
    }

    public function down(): void
    {
        Schema::table('parts_sale_works', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_f_kbn', 'office_code', 'sale_kbn', 'les_rate',
                'terminal_price', 'breakdown_code', 'invoice_kbn', 'invoice_m_kbn',
                'first_ship_kbn', 'open_kbn', 'dealer_code', 'standard_retail_price', 'filler',
            ]);
        });
    }
};
