<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    protected $fillable = [
        'model_code', 'color_code', 'model_name', 'model_abbr', 'base_model', 'model_name_kanji',
        'purchase_price', 'selling_price', 'terminal_price', 'standard_retail_price',
        'g1', 'g2', 'g3', 'g4', 'g5', 'order_number', 'tax_type',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'terminal_price' => 'decimal:2',
        'standard_retail_price' => 'decimal:2',
        'tax_type' => 'string',
    ];

    /** 税区分 */
    public const ZEI_KBN = [
        '0' => '非課税',
        '1' => '課税（10%）',
    ];

    /** タイプ区分（G1） */
    public const G1_TYPES = [
        'SS' => 'スポーツ',
        'TO' => 'ツーリング',
    ];

    /** 排気量区分（G2） */
    public const G2_DISP = [
        '01' => '50cc以下',
        '02' => '51〜125cc',
    ];

    /** G3（ダミー） */
    public const G3_OPTIONS = [
        '00' => 'ダミー',
    ];

    /** G4（ダミー） */
    public const G4_OPTIONS = [
        '00' => 'ダミー',
    ];

    /** G5（ダミー） */
    public const G5_OPTIONS = [
        '00' => 'ダミー',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search): Builder
    {
        return $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($q2) use ($search) {
                $q2->where('model_code', 'like', "%{$search}%")
                    ->orWhere('color_code', 'like', "%{$search}%")
                    ->orWhere('model_name', 'like', "%{$search}%")
                    ->orWhere('model_abbr', 'like', "%{$search}%")
                    ->orWhere('model_name_kanji', 'like', "%{$search}%")
                    ->orWhere('base_model', 'like', "%{$search}%");
            });
        });
    }
}
