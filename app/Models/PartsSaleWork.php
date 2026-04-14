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
        'monthly_f_type',
        'control_code',
        'part_number',
        'office_code',
        'slip_number',
        'order_qty',
        'order_date_raw',
        'order_date',
        'ship_qty',
        'sale_date_raw',
        'sale_date',
        'unit_price',
        'sale_type',
        'discount_rate',
        'partner_code',
        'cost_price',
        'terminal_price',
        'breakdown_code',
        'maintenance_no',
        'reversal_type',
        'invoice_type',
        'invoice_monthly_type',
        'dispatch_source',
        'staff_code',
        'rank_code',
        'first_shipment_type',
        'item_code',
        'item_name',
        'open_type',
        'dealer_code',
        'standard_retail_price',
        'model_group',
        'filler',
        'quantity',
        'model_code',
        'vehicle_code',
        'check_flag',
        'check_message',
        'converted_at',
    ];

    protected $casts = [
        'order_date'   => 'date:Y-m-d',
        'sale_date'    => 'date:Y-m-d',
        'order_qty'    => 'decimal:2',
        'ship_qty'     => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'cost_price'   => 'decimal:2',
        'quantity'     => 'decimal:2',
        'check_flag'   => 'integer',
        'converted_at' => 'datetime',
    ];

    public function scopeByYm(Builder $query, string $ym): Builder
    {
        return $query->where('processing_ym', $ym);
    }

    public function scopeFiltered(Builder $query, ?string $search): Builder
    {
        return $query->when($search !== null && $search !== '', function ($q) use ($search) {
            $q->where(function ($q2) use ($search) {
                $q2->where('part_number', 'like', "%{$search}%")
                    ->orWhere('slip_number', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%")
                    ->orWhere('partner_code', 'like', "%{$search}%")
                    ->orWhere('control_code', 'like', "%{$search}%")
                    ->orWhere('maintenance_no', 'like', "%{$search}%");
            });
        });
    }
}
