<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('kisyu_cd', 8);              // 機種コード
            $table->string('frame_no', 10);             // フレームNo
            $table->string('name1', 1000)->nullable();  // 商品名1
            $table->string('name2', 1000)->nullable();  // 商品名2
            $table->string('kisyu_nm', 1000)->nullable(); // 機種名
            $table->string('keishiki', 100)->nullable();  // 形式
            $table->string('kisyu_no', 20)->nullable();   // 機種番号
            $table->string('iro_cd', 6)->nullable();      // 色コード
            $table->decimal('sre_tan', 11, 2)->nullable(); // 仕入単価(税抜)
            $table->decimal('uri_tan', 11, 2)->nullable(); // 売上単価(税抜)
            $table->decimal('terminal_price', 11, 2)->nullable();        // 末端価格
            $table->decimal('standard_retail_price', 11, 2)->nullable(); // 標準小売価格
            $table->string('maker_code', 32)->nullable(); // メーカー品番
            $table->string('unit', 10)->nullable();       // 単位
            $table->string('note1', 1000)->nullable();    // 特記事項1
            $table->string('note2', 1000)->nullable();    // 特記事項2
            $table->string('note3', 1000)->nullable();    // 特記事項3
            $table->date('first_reg_date')->nullable();   // 初年度登録日
            $table->date('second_reg_date')->nullable();  // 2回目登録日
            $table->string('vehicle_no', 100)->nullable(); // 車両番号
            $table->string('owner_name', 200)->nullable(); // 氏名漢字
            $table->string('owner_kana', 200)->nullable(); // 氏名カナ
            $table->date('birth_date')->nullable();        // 生年月日
            $table->string('zip_code', 10)->nullable();    // 郵便番号
            $table->string('gender', 2)->nullable();       // 性別
            $table->string('address1', 200)->nullable();   // 住所1
            $table->string('address2', 200)->nullable();   // 住所2
            $table->string('tel', 20)->nullable();         // 連絡先
            $table->string('mobile', 20)->nullable();      // 携帯電話
            $table->boolean('has_security_reg')->default(false);   // G防犯登録有無
            $table->date('security_reg_date')->nullable();         // G防犯加入日
            $table->boolean('has_theft_insurance')->default(false); // 盗難保険有無
            $table->date('theft_insurance_date')->nullable();       // 盗難保険加入日
            $table->boolean('has_warranty')->default(false);       // 保証書登録票有無
            $table->boolean('has_application')->default(false);    // 申請書有無
            $table->boolean('has_dm')->default(false);             // DM発送有無
            $table->string('remarks', 1000)->nullable();           // 備考
            $table->string('shop_name', 1000)->nullable();         // 販売店名
            $table->date('sale_date')->nullable();                  // 売上日
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
