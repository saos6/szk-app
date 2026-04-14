<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    protected $fillable = [
        'stock_ym',
        'warehouse_code',
        'model_code',
        'frame_number',
        'prev_stock',
        'in_stock',
        'out_stock',
    ];

    protected $appends = ['current_stock'];

    protected $casts = [
        'is_deleted'    => 'boolean',
        'prev_stock'    => 'integer',
        'in_stock'      => 'integer',
        'out_stock'     => 'integer',
        'current_stock' => 'integer',
    ];

    /** 当月在庫数 = 前月繰越 + 当月入庫 - 当月出庫 */
    public function getCurrentStockAttribute(): int
    {
        return $this->prev_stock + $this->in_stock - $this->out_stock;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search, string $ymFrom = '', string $ymTo = ''): Builder
    {
        return $query
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('stock_ym', 'like', "%{$search}%")
                        ->orWhere('warehouse_code', 'like', "%{$search}%")
                        ->orWhere('model_code', 'like', "%{$search}%")
                        ->orWhere('frame_number', 'like', "%{$search}%");
                });
            })
            ->when($ymFrom !== '', fn ($q) => $q->where('stock_ym', '>=', $ymFrom))
            ->when($ymTo !== '', fn ($q) => $q->where('stock_ym', '<=', $ymTo));
    }
}
