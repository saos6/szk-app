<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            ['code' => 'WH001', 'name' => '第1倉庫'],
            ['code' => 'WH002', 'name' => '第2倉庫'],
            ['code' => 'WH003', 'name' => '第3倉庫'],
            ['code' => 'WH-OSK', 'name' => '大阪倉庫'],
            ['code' => 'WH-NGY', 'name' => '名古屋倉庫'],
        ];

        foreach ($warehouses as $data) {
            Warehouse::create($data);
        }
    }
}
