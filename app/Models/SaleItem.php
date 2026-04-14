<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'line_no',
        'vehicle_id',
        'model_code',
        'frame_number',
        'warehouse_code',
        'color_code',
        'model_name',
        'quantity',
        'unit',
        'purchase_price',
        'selling_price',
        'terminal_price',
        'tax_rate',
        'sale_amount',
        'cogs_amount',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'terminal_price' => 'decimal:2',
        'sale_amount' => 'decimal:2',
        'cogs_amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
