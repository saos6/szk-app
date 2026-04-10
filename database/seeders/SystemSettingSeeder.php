<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::firstOrCreate(
            ['id' => 1],
            ['closing_ym' => now()->format('Y-m')]
        );
    }
}
