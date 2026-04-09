<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\PaymentItem;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::active()->get()->keyBy('code');
        $employees = Employee::active()->get()->keyBy('code');

        $payments = [
            [
                'payment_number' => 'PM-202603-0001',
                'customer_code'  => 'C001',
                'employee_code'  => 'E001',
                'payment_date'   => '2026-03-31',
                'subject'        => 'CB400SF 販売代金 入金',
                'status'         => 'confirmed',
                'remarks'        => null,
                'items'          => [
                    ['payment_type' => 'transfer', 'amount' => 825000, 'bank_info' => '○○銀行 △△支店 普通 1234567', 'remarks' => null],
                ],
            ],
            [
                'payment_number' => 'PM-202604-0001',
                'customer_code'  => 'C002',
                'employee_code'  => 'E003',
                'payment_date'   => '2026-04-05',
                'subject'        => 'Ninja ZX-10R 入金',
                'status'         => 'confirmed',
                'remarks'        => null,
                'items'          => [
                    ['payment_type' => 'transfer', 'amount' => 1980000, 'bank_info' => '□□銀行 ××支店 普通 7654321', 'remarks' => null],
                    ['payment_type' => 'fee',      'amount' => 440,     'bank_info' => null, 'remarks' => '振込手数料'],
                ],
            ],
            [
                'payment_number' => 'PM-202604-0002',
                'customer_code'  => 'C003',
                'employee_code'  => 'E001',
                'payment_date'   => '2026-04-08',
                'subject'        => '4月分まとめ入金',
                'status'         => 'draft',
                'remarks'        => '分割払い第1回',
                'items'          => [
                    ['payment_type' => 'cash',     'amount' => 500000, 'bank_info' => null, 'remarks' => '頭金'],
                    ['payment_type' => 'transfer', 'amount' => 300000, 'bank_info' => '△△銀行 本店 普通 9988776', 'remarks' => null],
                ],
            ],
        ];

        foreach ($payments as $data) {
            $total = array_sum(array_column($data['items'], 'amount'));

            $payment = Payment::create([
                'payment_number' => $data['payment_number'],
                'customer_id'    => $customers->get($data['customer_code'])?->id,
                'employee_id'    => $employees->get($data['employee_code'])?->id,
                'payment_date'   => $data['payment_date'],
                'subject'        => $data['subject'],
                'status'         => $data['status'],
                'total_amount'   => $total,
                'remarks'        => $data['remarks'],
            ]);

            foreach ($data['items'] as $i => $item) {
                PaymentItem::create([
                    'payment_id'   => $payment->id,
                    'line_no'      => $i + 1,
                    'payment_type' => $item['payment_type'],
                    'amount'       => $item['amount'],
                    'bank_info'    => $item['bank_info'],
                    'remarks'      => $item['remarks'],
                ]);
            }
        }
    }
}
