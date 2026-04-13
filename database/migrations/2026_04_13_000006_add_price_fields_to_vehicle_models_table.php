<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_models', function (Blueprint $table) {
            $table->decimal('terminal_price', 11, 2)->nullable()->after('uri_tan');        // 末端価格
            $table->decimal('standard_retail_price', 11, 2)->nullable()->after('terminal_price'); // 標準小売価格
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_models', function (Blueprint $table) {
            $table->dropColumn(['terminal_price', 'standard_retail_price']);
        });
    }
};
