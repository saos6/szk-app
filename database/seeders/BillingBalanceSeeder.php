<?php

namespace Database\Seeders;

use App\Models\BillingBalance;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class BillingBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::active()->get()->keyBy('code');

        $records = [
            // 2026年2月請求分
            [
                'customer_code' => 'C001',
                'billing_date'  => '2026-02-28',
                'prev_amount'   => 0,
                'sales_amount'  => 850000,
                'tax_amount'    => 85000,
                'payment_amount'=> 935000,
            ],
            [
                'customer_code' => 'C002',
                'billing_date'  => '2026-02-28',
                'prev_amount'   => 0,
                'sales_amount'  => 320000,
                'tax_amount'    => 32000,
                'payment_amount'=> 352000,
            ],
            [
                'customer_code' => 'C003',
                'billing_date'  => '2026-02-28',
                'prev_amount'   => 0,
                'sales_amount'  => 1200000,
                'tax_amount'    => 120000,
                'payment_amount'=> 1000000,
            ],
            // 2026年3月請求分
            [
                'customer_code' => 'C001',
                'billing_date'  => '2026-03-31',
                'prev_amount'   => 0,
                'sales_amount'  => 1320000,
                'tax_amount'    => 132000,
                'payment_amount'=> 1452000,
            ],
            [
                'customer_code' => 'C002',
                'billing_date'  => '2026-03-31',
                'prev_amount'   => 0,
                'sales_amount'  => 1980000,
                'tax_amount'    => 198000,
                'payment_amount'=> 2178000,
            ],
            [
                'customer_code' => 'C003',
                'billing_date'  => '2026-03-31',
                'prev_amount'   => 320000,
                'sales_amount'  => 0,
                'tax_amount'    => 0,
                'payment_amount'=> 320000,
            ],
            [
                'customer_code' => 'C004',
                'billing_date'  => '2026-03-31',
                'prev_amount'   => 0,
                'sales_amount'  => 2156000,
                'tax_amount'    => 215600,
                'payment_amount'=> 0,
            ],
            [
                'customer_code' => 'C005',
                'billing_date'  => '2026-03-31',
                'prev_amount'   => 0,
                'sales_amount'  => 346500,
                'tax_amount'    => 0,
                'payment_amount'=> 346500,
            ],
            // 2026年4月請求分
            [
                'customer_code' => 'C001',
                'billing_date'  => '2026-04-30',
                'prev_amount'   => 0,
                'sales_amount'  => 990000,
                'tax_amount'    => 99000,
                'payment_amount'=> 500000,
            ],
            [
                'customer_code' => 'C004',
                'billing_date'  => '2026-04-30',
                'prev_amount'   => 2371600,
                'sales_amount'  => 2541600,
                'tax_amount'    => 254160,
                'payment_amount'=> 2371600,
            ],
        ];

        foreach ($records as $data) {
            $customer = $customers->get($data['customer_code']);
            if (! $customer) {
                continue;
            }

            $sales   = (float) $data['sales_amount'];
            $tax     = (float) $data['tax_amount'];
            $total   = round($sales + $tax, 2);

            BillingBalance::create([
                'billing_date'   => $data['billing_date'],
                'customer_id'    => $customer->id,
                'prev_amount'    => $data['prev_amount'],
                'sales_amount'   => $sales,
                'tax_amount'     => $tax,
                'total_amount'   => $total,
                'payment_amount' => $data['payment_amount'],
            ]);
        }
    }
}
