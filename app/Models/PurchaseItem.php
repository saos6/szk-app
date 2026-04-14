<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
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
        'purchase_amount',
        'tax_rate',
        'remarks',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'purchase_price'  => 'decimal:2',
        'purchase_amount' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
