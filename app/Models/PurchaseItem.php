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
        'kisyu_cd',
        'frame_no',
        'warehouse_code',
        'iro_cd',
        'kisyu_nm',
        'quantity',
        'unit',
        'sre_tan',
        'purchase_amount',
        'tax_rate',
        'remarks',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'sre_tan'         => 'decimal:2',
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
