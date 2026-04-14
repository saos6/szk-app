<?php

namespace Database\Seeders;

use App\Models\InventoryBalance;
use Illuminate\Database\Seeder;

class InventoryBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            // 2026-03 データ
            ['stock_ym' => '2026-03', 'warehouse_code' => 'WH001', 'model_code' => 'CB400SF', 'frame_number' => 'CB400-0001', 'prev_stock' => 5,  'in_stock' => 10, 'out_stock' => 8],
            ['stock_ym' => '2026-03', 'warehouse_code' => 'WH001', 'model_code' => 'CB400SF', 'frame_number' => 'CB400-0002', 'prev_stock' => 3,  'in_stock' => 5,  'out_stock' => 3],
            ['stock_ym' => '2026-03', 'warehouse_code' => 'WH002', 'model_code' => 'CB400SF', 'frame_number' => 'CB400-0003', 'prev_stock' => 0,  'in_stock' => 8,  'out_stock' => 5],
            ['stock_ym' => '2026-03', 'warehouse_code' => 'WH001', 'model_code' => 'ZX10R',  'frame_number' => 'ZX10R-0001', 'prev_stock' => 2,  'in_stock' => 4,  'out_stock' => 2],
            ['stock_ym' => '2026-03', 'warehouse_code' => 'WH-OSK', 'model_code' => 'CB400SF', 'frame_number' => 'CB400-0010', 'prev_stock' => 10, 'in_stock' => 20, 'out_stock' => 15],

            // 2026-04 データ
            ['stock_ym' => '2026-04', 'warehouse_code' => 'WH001', 'model_code' => 'CB400SF', 'frame_number' => 'CB400-0001', 'prev_stock' => 7,  'in_stock' => 12, 'out_stock' => 6],
            ['stock_ym' => '2026-04', 'warehouse_code' => 'WH001', 'model_code' => 'CB400SF', 'frame_number' => 'CB400-0002', 'prev_stock' => 5,  'in_stock' => 3,  'out_stock' => 4],
            ['stock_ym' => '2026-04', 'warehouse_code' => 'WH002', 'model_code' => 'CB400SF', 'frame_number' => 'CB400-0003', 'prev_stock' => 3,  'in_stock' => 6,  'out_stock' => 2],
            ['stock_ym' => '2026-04', 'warehouse_code' => 'WH001', 'model_code' => 'ZX10R',  'frame_number' => 'ZX10R-0001', 'prev_stock' => 4,  'in_stock' => 2,  'out_stock' => 3],
            ['stock_ym' => '2026-04', 'warehouse_code' => 'WH-OSK', 'model_code' => 'CB400SF', 'frame_number' => 'CB400-0010', 'prev_stock' => 15, 'in_stock' => 10, 'out_stock' => 8],
        ];

        foreach ($records as $data) {
            InventoryBalance::create($data);
        }
    }
}
