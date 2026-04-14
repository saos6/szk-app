<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->unsignedSmallInteger('line_no');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->string('model_code', 8)->nullable();
            $table->string('frame_number', 30)->nullable();
            $table->string('warehouse_code', 20)->nullable();
            $table->string('color_code', 6)->nullable();
            $table->string('model_name', 200)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 10)->nullable();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('purchase_amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('tax_rate')->default(10);
            $table->string('remarks', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
