<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingBalance extends Model
{
    protected $fillable = [
        'billing_date',
        'customer_id',
        'prev_amount',
        'sales_amount',
        'tax_amount',
        'total_amount',
        'payment_amount',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'billing_date' => 'date',
        'prev_amount' => 'decimal:2',
        'sales_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search): Builder
    {
        return $query->when($search !== '', function ($q) use ($search) {
            $q->whereHas('customer', function ($c) use ($search) {
                $c->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        });
    }
}
