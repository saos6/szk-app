<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::active()->get()->keyBy('code');
        $employees = Employee::active()->get()->keyBy('code');
        $vehicles  = Vehicle::active()->get()->keyBy('frame_number');

        $purchases = [
            [
                'purchase_number' => 'PU-202603-0001',
                'supplier_code'   => 'S001',
                'employee_code'   => 'E001',
                'purchase_date'   => '2026-03-20',
                'subject'         => 'CB400スーパーフォア 仕入',
                'status'          => 'completed',
                'remarks'         => null,
                'items' => [
                    ['frame_number' => 'NC31100001', 'quantity' => 1, 'tax_rate' => '10'],
                ],
            ],
            [
                'purchase_number' => 'PU-202603-0002',
                'supplier_code'   => 'S003',
                'employee_code'   => 'E003',
                'purchase_date'   => '2026-03-22',
                'subject'         => 'Ninja ZX-10R 仕入',
                'status'          => 'completed',
                'remarks'         => '特別カラー限定モデル',
                'items' => [
                    ['frame_number' => 'ZXT00E0001', 'quantity' => 1, 'tax_rate' => '10'],
                ],
            ],
            [
                'purchase_number' => 'PU-202604-0001',
                'supplier_code'   => 'S002',
                'employee_code'   => 'E001',
                'purchase_date'   => '2026-04-03',
                'subject'         => 'MT-09 SP / PCX160 仕入',
                'status'          => 'recorded',
                'remarks'         => null,
                'items' => [
                    ['frame_number' => 'B3L0000001',   'quantity' => 1, 'tax_rate' => '10'],
                    ['frame_number' => 'JK05E1000001', 'quantity' => 1, 'tax_rate' => '10'],
                ],
            ],
            [
                'purchase_number' => 'PU-202604-0002',
                'supplier_code'   => 'S004',
                'employee_code'   => 'E004',
                'purchase_date'   => '2026-04-06',
                'subject'         => 'GSX-R1000R 仕入',
                'status'          => 'draft',
                'remarks'         => null,
                'items' => [
                    ['frame_number' => 'GT79A100001', 'quantity' => 1, 'tax_rate' => '10'],
                ],
            ],
        ];

        foreach ($purchases as $purchaseData) {
            $subtotal  = 0;
            $taxAmount = 0;
            $itemRows  = [];

            foreach ($purchaseData['items'] as $i => $itemDef) {
                $vehicle = $vehicles->get($itemDef['frame_number']);
                $qty     = (float) $itemDef['quantity'];
                $sre     = $vehicle ? (float) $vehicle->purchase_price : 0;
                $rate    = (int) $itemDef['tax_rate'];
                $amt     = round($qty * $sre, 2);

                $subtotal  += $amt;
                $taxAmount += round($amt * $rate / 100, 2);

                $itemRows[] = [
                    'line_no'         => $i + 1,
                    'vehicle_id'      => $vehicle?->id,
                    'model_code'      => $vehicle?->model_code,
                    'frame_number'    => $itemDef['frame_number'],
                    'warehouse_code'  => null,
                    'color_code'      => $vehicle?->color_code,
                    'model_name'      => $vehicle?->model_name,
                    'quantity'        => $qty,
                    'unit'            => $vehicle?->unit ?? '台',
                    'purchase_price'  => $sre,
                    'purchase_amount' => $amt,
                    'tax_rate'        => $itemDef['tax_rate'],
                    'remarks'         => null,
                ];
            }

            $purchase = Purchase::create([
                'purchase_number' => $purchaseData['purchase_number'],
                'supplier_id'     => $suppliers->get($purchaseData['supplier_code'])?->id,
                'employee_id'     => $employees->get($purchaseData['employee_code'])?->id,
                'purchase_date'   => $purchaseData['purchase_date'],
                'subject'         => $purchaseData['subject'],
                'status'          => $purchaseData['status'],
                'subtotal'        => round($subtotal, 2),
                'tax_amount'      => round($taxAmount, 2),
                'total_amount'    => round($subtotal + $taxAmount, 2),
                'remarks'         => $purchaseData['remarks'],
            ]);

            foreach ($itemRows as $row) {
                PurchaseItem::create(array_merge(['purchase_id' => $purchase->id], $row));
            }
        }
    }
}
