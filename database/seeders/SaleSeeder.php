<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::active()->get()->keyBy('code');
        $employees = Employee::active()->get()->keyBy('code');
        $vehicles = Vehicle::active()->get()->keyBy('frame_no');

        $sales = [
            [
                'sale_number' => 'SA-202603-0001',
                'customer_code' => 'C001',
                'employee_code' => 'E001',
                'sale_date' => '2026-03-25',
                'delivery_date' => '2026-03-28',
                'subject' => 'CB400スーパーフォア 販売',
                'status' => 'completed',
                'remarks' => null,
                'items' => [
                    ['frame_no' => 'NC31100001', 'quantity' => 1, 'tax_rate' => '10'],
                ],
            ],
            [
                'sale_number' => 'SA-202603-0002',
                'customer_code' => 'C002',
                'employee_code' => 'E003',
                'sale_date' => '2026-03-28',
                'delivery_date' => '2026-04-02',
                'subject' => 'Ninja ZX-10R 販売',
                'status' => 'completed',
                'remarks' => '特別カラー限定モデル',
                'items' => [
                    ['frame_no' => 'ZXT00E0001', 'quantity' => 1, 'tax_rate' => '10'],
                ],
            ],
            [
                'sale_number' => 'SA-202604-0001',
                'customer_code' => 'C003',
                'employee_code' => 'E001',
                'sale_date' => '2026-04-05',
                'delivery_date' => null,
                'subject' => 'MT-09 SP / PCX160 販売',
                'status' => 'draft',
                'remarks' => null,
                'items' => [
                    ['frame_no' => 'B3L0000001',  'quantity' => 1, 'tax_rate' => '10'],
                    ['frame_no' => 'JK05E1000001', 'quantity' => 1, 'tax_rate' => '10'],
                ],
            ],
            [
                'sale_number' => 'SA-202604-0002',
                'customer_code' => 'C004',
                'employee_code' => 'E004',
                'sale_date' => '2026-04-07',
                'delivery_date' => '2026-04-07',
                'subject' => 'GSX-R1000R 販売',
                'status' => 'recorded',
                'remarks' => 'レース仕様オプション取付',
                'items' => [
                    ['frame_no' => 'GT79A100001', 'quantity' => 1, 'tax_rate' => '10'],
                ],
            ],
            [
                'sale_number' => 'SA-202604-0003',
                'customer_code' => 'C005',
                'employee_code' => 'E003',
                'sale_date' => '2026-04-08',
                'delivery_date' => null,
                'subject' => 'モンキー125 / CRF50F 販売',
                'status' => 'draft',
                'remarks' => 'お子様向けセット販売',
                'items' => [
                    ['frame_no' => 'JB02E5000001',  'quantity' => 1, 'tax_rate' => '10'],
                    ['frame_no' => 'AE01E0100001', 'quantity' => 1, 'tax_rate' => '0'],
                ],
            ],
        ];

        foreach ($sales as $saleData) {
            $subtotal = 0;
            $taxAmount = 0;
            $cogsTotal = 0;
            $itemRows = [];

            foreach ($saleData['items'] as $i => $itemDef) {
                $vehicle = $vehicles->get($itemDef['frame_no']);
                $qty = (float) $itemDef['quantity'];
                $uri = $vehicle ? (float) $vehicle->uri_tan : 0;
                $sre = $vehicle ? (float) $vehicle->sre_tan : 0;
                $rate = (int) $itemDef['tax_rate'];
                $saleAmt = round($qty * $uri, 2);
                $cogsAmt = round($qty * $sre, 2);

                $subtotal += $saleAmt;
                $taxAmount += round($saleAmt * $rate / 100, 2);
                $cogsTotal += $cogsAmt;

                $itemRows[] = [
                    'line_no' => $i + 1,
                    'vehicle_id' => $vehicle?->id,
                    'kisyu_cd' => $vehicle?->kisyu_cd,
                    'frame_no' => $itemDef['frame_no'],
                    'warehouse_code' => $itemDef['warehouse_code'] ?? null,
                    'iro_cd' => $vehicle?->iro_cd,
                    'kisyu_nm' => $vehicle?->kisyu_nm,
                    'quantity' => $qty,
                    'unit' => $vehicle?->unit ?? '台',
                    'sre_tan' => $sre,
                    'uri_tan' => $uri,
                    'tax_rate' => $itemDef['tax_rate'],
                    'sale_amount' => $saleAmt,
                    'cogs_amount' => $cogsAmt,
                    'remarks' => null,
                ];
            }

            $sale = Sale::create([
                'sale_number' => $saleData['sale_number'],
                'customer_id' => $customers->get($saleData['customer_code'])?->id,
                'employee_id' => $employees->get($saleData['employee_code'])?->id,
                'sale_date' => $saleData['sale_date'],
                'delivery_date' => $saleData['delivery_date'],
                'subject' => $saleData['subject'],
                'status' => $saleData['status'],
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'total_amount' => round($subtotal + $taxAmount, 2),
                'cogs_total' => round($cogsTotal, 2),
                'remarks' => $saleData['remarks'],
            ]);

            foreach ($itemRows as $row) {
                SaleItem::create(array_merge(['sale_id' => $sale->id], $row));
            }
        }
    }
}
