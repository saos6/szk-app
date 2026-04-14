<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('system_settings', 'company_name')) {
                $table->string('company_name', 100)->nullable()->after('closing_ym')->comment('会社名');
            }
            if (! Schema::hasColumn('system_settings', 'company_name_kana')) {
                $table->string('company_name_kana', 100)->nullable()->after('company_name')->comment('会社名カナ');
            }
            if (! Schema::hasColumn('system_settings', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('company_name_kana')->comment('郵便番号');
            }
            if (! Schema::hasColumn('system_settings', 'prefecture_city')) {
                $table->string('prefecture_city', 100)->nullable()->after('postal_code')->comment('都道府県市町村');
            }
            if (! Schema::hasColumn('system_settings', 'address')) {
                $table->string('address', 200)->nullable()->after('prefecture_city')->comment('番地');
            }
            if (! Schema::hasColumn('system_settings', 'building')) {
                $table->string('building', 200)->nullable()->after('address')->comment('ビル等');
            }
            if (! Schema::hasColumn('system_settings', 'representative')) {
                $table->string('representative', 100)->nullable()->after('building')->comment('代表者');
            }
            if (! Schema::hasColumn('system_settings', 'tel')) {
                $table->string('tel', 20)->nullable()->after('representative')->comment('TEL番号');
            }
            if (! Schema::hasColumn('system_settings', 'fax')) {
                $table->string('fax', 20)->nullable()->after('tel')->comment('FAX番号');
            }
            if (! Schema::hasColumn('system_settings', 'invoice_no')) {
                $table->string('invoice_no', 20)->nullable()->after('fax')->comment('インボイス登録番号');
            }
            if (! Schema::hasColumn('system_settings', 'bank_info')) {
                $table->string('bank_info', 200)->nullable()->after('invoice_no')->comment('銀行情報');
            }
            if (! Schema::hasColumn('system_settings', 'account_number')) {
                $table->string('account_number', 50)->nullable()->after('bank_info')->comment('口座番号');
            }
            if (! Schema::hasColumn('system_settings', 'account_holder')) {
                $table->string('account_holder', 100)->nullable()->after('account_number')->comment('口座名義人名');
            }
            if (! Schema::hasColumn('system_settings', 'remarks')) {
                $table->text('remarks')->nullable()->after('account_holder')->comment('備考');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'company_name', 'company_name_kana', 'postal_code',
                'prefecture_city', 'address', 'building', 'representative',
                'tel', 'fax', 'invoice_no', 'bank_info',
                'account_number', 'account_holder', 'remarks',
            ]);
        });
    }
};
