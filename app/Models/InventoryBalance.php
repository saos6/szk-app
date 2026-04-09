<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    protected $fillable = [
        'stock_ym',
        'warehouse_code',
        'vehicle_model_code',
        'frame_no',
        'prev_stock',
        'in_stock',
        'out_stock',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'prev_stock'  => 'integer',
        'in_stock'    => 'integer',
        'out_stock'   => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search): Builder
    {
        return $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($q2) use ($search) {
                $q2->where('stock_ym', 'like', "%{$search}%")
                    ->orWhere('warehouse_code', 'like', "%{$search}%")
                    ->orWhere('vehicle_model_code', 'like', "%{$search}%")
                    ->orWhere('frame_no', 'like', "%{$search}%");
            });
        });
    }
}
