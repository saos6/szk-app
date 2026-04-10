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

        // 4月分は請求締め処理で生成するためシーダーには含めない
        $records = [
            // 2026年2月請求分
            [
                'customer_code'  => 'C001',
                'billing_date'   => '2026-02-28',
                'prev_amount'    => 0,
                'sales_amount'   => 850000,
                'tax_amount'     => 85000,
                'payment_amount' => 935000,
            ],
            [
                'customer_code'  => 'C002',
                'billing_date'   => '2026-02-28',
                'prev_amount'    => 0,
                'sales_amount'   => 320000,
                'tax_amount'     => 32000,
                'payment_amount' => 352000,
            ],
            [
                'customer_code'  => 'C003',
                'billing_date'   => '2026-02-28',
                'prev_amount'    => 0,
                'sales_amount'   => 1200000,
                'tax_amount'     => 120000,
                'payment_amount' => 1000000,
            ],
            // 2026年3月請求分
            [
                'customer_code'  => 'C001',
                'billing_date'   => '2026-03-31',
                'prev_amount'    => 0,
                'sales_amount'   => 1320000,
                'tax_amount'     => 132000,
                'payment_amount' => 1452000,
            ],
            [
                'customer_code'  => 'C002',
                'billing_date'   => '2026-03-31',
                'prev_amount'    => 0,
                'sales_amount'   => 1980000,
                'tax_amount'     => 198000,
                'payment_amount' => 2178000,
            ],
            [
                'customer_code'  => 'C003',
                'billing_date'   => '2026-03-31',
                'prev_amount'    => 320000,
                'sales_amount'   => 0,
                'tax_amount'     => 0,
                'payment_amount' => 320000,
            ],
            [
                'customer_code'  => 'C004',
                'billing_date'   => '2026-03-31',
                'prev_amount'    => 0,
                'sales_amount'   => 2156000,
                'tax_amount'     => 215600,
                'payment_amount' => 0,
            ],
            [
                'customer_code'  => 'C005',
                'billing_date'   => '2026-03-31',
                'prev_amount'    => 0,
                'sales_amount'   => 346500,
                'tax_amount'     => 0,
                'payment_amount' => 346500,
            ],
        ];

        foreach ($records as $data) {
            $customer = $customers->get($data['customer_code']);
            if (! $customer) {
                continue;
            }

            $prev    = (float) ($data['prev_amount'] ?? 0);
            $sales   = (float) $data['sales_amount'];
            $tax     = (float) $data['tax_amount'];
            $pay     = (float) $data['payment_amount'];
            $total   = round($prev + $sales + $tax, 2);
            $balance = round($total - $pay, 2);

            BillingBalance::create([
                'billing_number'  => BillingBalance::generateBillingNumber($data['billing_date']),
                'billing_date'    => $data['billing_date'],
                'customer_id'     => $customer->id,
                'prev_amount'     => $prev,
                'sales_amount'    => $sales,
                'tax_amount'      => $tax,
                'total_amount'    => $total,
                'payment_amount'  => $pay,
                'balance_amount'  => $balance,
                'status'          => 'confirmed',
            ]);
        }
    }
}
