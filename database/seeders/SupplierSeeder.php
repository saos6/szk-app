<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code'           => 'S001',
                'name'           => 'ホンダモーターサイクルジャパン株式会社',
                'name_kana'      => 'ホンダモーターサイクルジャパンカブシキガイシャ',
                'postal_code'    => '107-8556',
                'address'        => '東京都港区南青山2-1-1',
                'phone'          => '03-3423-1111',
                'fax'            => '03-3423-1112',
                'email'          => 'info@honda-mc.co.jp',
                'contact_person' => '鈴木 太郎',
                'payment_site'   => 60,
                'remarks'        => null,
            ],
            [
                'code'           => 'S002',
                'name'           => 'ヤマハ発動機販売株式会社',
                'name_kana'      => 'ヤマハハツドウキハンバイカブシキガイシャ',
                'postal_code'    => '430-8650',
                'address'        => '静岡県浜松市中区新町200',
                'phone'          => '053-460-2211',
                'fax'            => '053-460-2212',
                'email'          => 'sales@yamaha-motor.co.jp',
                'contact_person' => '田中 一郎',
                'payment_site'   => 60,
                'remarks'        => null,
            ],
            [
                'code'           => 'S003',
                'name'           => 'カワサキモータース株式会社',
                'name_kana'      => 'カワサキモータースカブシキガイシャ',
                'postal_code'    => '650-8680',
                'address'        => '兵庫県神戸市中央区東川崎町3-1-1',
                'phone'          => '078-682-5001',
                'fax'            => '078-682-5002',
                'email'          => 'dealer@kawasaki-motors.co.jp',
                'contact_person' => '中村 健二',
                'payment_site'   => 90,
                'remarks'        => 'スポーツバイク専門',
            ],
            [
                'code'           => 'S004',
                'name'           => 'スズキ株式会社 二輪事業部',
                'name_kana'      => 'スズキカブシキガイシャニリンジギョウブ',
                'postal_code'    => '432-8611',
                'address'        => '静岡県浜松市南区高塚町300',
                'phone'          => '053-440-2011',
                'fax'            => '053-440-2012',
                'email'          => 'moto@suzuki.co.jp',
                'contact_person' => '山本 隆',
                'payment_site'   => 60,
                'remarks'        => null,
            ],
            [
                'code'           => 'S005',
                'name'           => '二輪パーツ卸売センター株式会社',
                'name_kana'      => 'ニリンパーツオロシウリセンターカブシキガイシャ',
                'postal_code'    => '556-0012',
                'address'        => '大阪府大阪市浪速区敷津東1-6-1',
                'phone'          => '06-6631-2000',
                'fax'            => '06-6631-2001',
                'email'          => 'order@parts-center.co.jp',
                'contact_person' => '西田 次郎',
                'payment_site'   => 30,
                'remarks'        => '消耗品・アクセサリー専門',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::create($data);
        }
    }
}
