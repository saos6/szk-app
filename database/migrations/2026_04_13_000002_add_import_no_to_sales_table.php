<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('import_no', 20)->nullable()->after('sale_number')
                ->comment('部品売上取込 伝票NO（重複チェック用）');
            $table->index('import_no');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['import_no']);
            $table->dropColumn('import_no');
        });
    }
};
