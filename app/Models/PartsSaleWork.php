<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PartsSaleWork extends Model
{
    public const CHECK_NORMAL = 0;
    public const CHECK_ERROR  = 1;

    protected $fillable = [
        'processing_ym',
        'monthly_f_kbn',
        'control_code',
        'hinban',
        'office_code',
        'slip_no',
        'order_qty',
        'order_date_raw',
        'order_date',
        'ship_qty',
        'sale_date_raw',
        'sale_date',
        'unit_price',
        'sale_kbn',
        'les_rate',
        'partner_code',
        'cost_price',
        'terminal_price',
        'breakdown_code',
        'maintenance_no',
        'red_black_kbn',
        'invoice_kbn',
        'invoice_m_kbn',
        'dispatch_source',
        'staff_code',
        'rank_cd',
        'first_ship_kbn',
        'item_code',
        'item_name',
        'open_kbn',
        'dealer_code',
        'standard_retail_price',
        'model_group',
        'filler',
        'quantity',
        'model_kisyu_cd',
        'vehicle_kisyu_cd',
        'check_flag',
        'check_message',
    ];

    protected $casts = [
        'order_date'  => 'date:Y-m-d',
        'sale_date'   => 'date:Y-m-d',
        'order_qty'   => 'decimal:2',
        'ship_qty'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'cost_price'  => 'decimal:2',
        'quantity'    => 'decimal:2',
        'check_flag'  => 'integer',
    ];

    public function scopeByYm(Builder $query, string $ym): Builder
    {
        return $query->where('processing_ym', $ym);
    }
}
