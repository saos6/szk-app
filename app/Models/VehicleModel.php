<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    protected $fillable = [
        'kisyu_cd', 'iro_cd', 'kisyu_nm', 'kisyu_nm_r', 'kihon', 'kisyu_nm_h',
        'sre_tan', 'uri_tan', 'g1', 'g2', 'g3', 'g4', 'g5', 'order_no', 'zei_kbn',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'sre_tan' => 'decimal:2',
        'uri_tan' => 'decimal:2',
        'zei_kbn' => 'string',
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
                $q2->where('kisyu_cd', 'like', "%{$search}%")
                    ->orWhere('iro_cd', 'like', "%{$search}%")
                    ->orWhere('kisyu_nm', 'like', "%{$search}%")
                    ->orWhere('kisyu_nm_r', 'like', "%{$search}%")
                    ->orWhere('kisyu_nm_h', 'like', "%{$search}%")
                    ->orWhere('kihon', 'like', "%{$search}%");
            });
        });
    }
}
