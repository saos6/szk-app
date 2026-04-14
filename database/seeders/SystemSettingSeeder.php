<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::updateOrCreate(
            ['id' => 1],
            [
                'closing_ym'        => now()->format('Y-m'),
                'company_name'      => '株式会社沖縄スズキ販売',
                'company_name_kana' => 'カブシキガイシャオキナワスズキハンバイ',
                'postal_code'       => '900-0015',
                'prefecture_city'   => '沖縄県那覇市',
                'address'           => '久茂地1-2-3',
                'building'          => 'スズキビル2F',
                'representative'    => '山田 太郎',
                'tel'               => '098-123-4567',
                'fax'               => '098-123-4568',
                'invoice_no'        => 'T1234567890123',
                'bank_info'         => '琉球銀行 那覇支店 普通',
                'account_number'    => '1234567',
                'account_holder'    => 'カ）オキナワスズキハンバイ',
                'remarks'           => null,
            ]
        );
    }
}
