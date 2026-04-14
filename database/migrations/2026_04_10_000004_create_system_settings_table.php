<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('closing_ym', 7)->comment('月次更新年月 YYYY-MM');
            $table->string('company_name', 100)->nullable()->comment('会社名');
            $table->string('company_name_kana', 100)->nullable()->comment('会社名カナ');
            $table->string('postal_code', 10)->nullable()->comment('郵便番号');
            $table->string('prefecture_city', 100)->nullable()->comment('都道府県市町村');
            $table->string('address', 200)->nullable()->comment('番地');
            $table->string('building', 200)->nullable()->comment('ビル等');
            $table->string('representative', 100)->nullable()->comment('代表者');
            $table->string('tel', 20)->nullable()->comment('TEL番号');
            $table->string('fax', 20)->nullable()->comment('FAX番号');
            $table->string('invoice_no', 20)->nullable()->comment('インボイス登録番号');
            $table->string('bank_info', 200)->nullable()->comment('銀行情報');
            $table->string('account_number', 50)->nullable()->comment('口座番号');
            $table->string('account_holder', 100)->nullable()->comment('口座名義人名');
            $table->text('remarks')->nullable()->comment('備考');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
