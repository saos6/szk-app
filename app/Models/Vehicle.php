<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'kisyu_cd', 'frame_no', 'name1', 'name2', 'kisyu_nm', 'keishiki', 'kisyu_no',
        'iro_cd', 'sre_tan', 'uri_tan', 'maker_code', 'unit',
        'note1', 'note2', 'note3',
        'first_reg_date', 'second_reg_date', 'vehicle_no',
        'owner_name', 'owner_kana', 'birth_date', 'zip_code', 'gender',
        'address1', 'address2', 'tel', 'mobile',
        'has_security_reg', 'security_reg_date',
        'has_theft_insurance', 'theft_insurance_date',
        'has_warranty', 'has_application', 'has_dm',
        'remarks', 'shop_name', 'sale_date',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'sre_tan' => 'decimal:2',
        'uri_tan' => 'decimal:2',
        'has_security_reg' => 'boolean',
        'has_theft_insurance' => 'boolean',
        'has_warranty' => 'boolean',
        'has_application' => 'boolean',
        'has_dm' => 'boolean',
        'first_reg_date' => 'date:Y-m-d',
        'second_reg_date' => 'date:Y-m-d',
        'birth_date' => 'date:Y-m-d',
        'security_reg_date' => 'date:Y-m-d',
        'theft_insurance_date' => 'date:Y-m-d',
        'sale_date' => 'date:Y-m-d',
    ];

    /** 性別 */
    public const GENDERS = [
        'M' => '男',
        'F' => '女',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search): Builder
    {
        return $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($q2) use ($search) {
                $q2->where('kisyu_cd', 'like', "%{$search}%")
                    ->orWhere('frame_no', 'like', "%{$search}%")
                    ->orWhere('kisyu_nm', 'like', "%{$search}%")
                    ->orWhere('vehicle_no', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('owner_kana', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%");
            });
        });
    }
}
