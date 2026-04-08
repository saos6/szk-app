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
        'kisyu_cd',
        'frame_no',
        'iro_cd',
        'kisyu_nm',
        'quantity',
        'unit',
        'sre_tan',
        'uri_tan',
        'tax_rate',
        'sale_amount',
        'cogs_amount',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'sre_tan' => 'decimal:2',
        'uri_tan' => 'decimal:2',
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
