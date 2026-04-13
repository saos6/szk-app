<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts_sale_works', function (Blueprint $table) {
            $table->timestamp('converted_at')->nullable()->after('check_message'); // 売上変換日時
        });
    }

    public function down(): void
    {
        Schema::table('parts_sale_works', function (Blueprint $table) {
            $table->dropColumn('converted_at');
        });
    }
};
