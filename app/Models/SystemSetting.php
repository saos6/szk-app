<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'closing_ym',
        'company_name',
        'company_name_kana',
        'postal_code',
        'prefecture_city',
        'address',
        'building',
        'representative',
        'tel',
        'fax',
        'invoice_no',
        'bank_info',
        'account_number',
        'account_holder',
        'remarks',
    ];

    /**
     * ID=1 固定のシングルトンレコードを取得する。
     * 存在しない場合は当月で初期化する。
     */
    public static function instance(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            ['closing_ym' => now()->format('Y-m')]
        );
    }
}
