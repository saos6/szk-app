<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingBalance extends Model
{
    public const STATUSES = [
        'confirmed' => '確定',
        'cancelled' => '取消',
    ];

    protected $fillable = [
        'billing_number',
        'billing_date',
        'closing_start_date',
        'customer_id',
        'prev_amount',
        'sales_amount',
        'tax_amount',
        'total_amount',
        'payment_amount',
        'balance_amount',
        'status',
        'remarks',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'is_deleted'         => 'boolean',
        'billing_date'       => 'date:Y-m-d',
        'closing_start_date' => 'date:Y-m-d',
        'prev_amount'        => 'decimal:2',
        'sales_amount'       => 'decimal:2',
        'tax_amount'         => 'decimal:2',
        'total_amount'       => 'decimal:2',
        'payment_amount'     => 'decimal:2',
        'balance_amount'     => 'decimal:2',
        'cancelled_at'       => 'datetime',
    ];

    // ─── Relationships ──────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'cancelled_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search, string $dateFrom = '', string $dateTo = ''): Builder
    {
        return $query
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('customer', function ($c) use ($search) {
                    $c->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($dateFrom !== '', fn ($q) => $q->where('billing_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->where('billing_date', '<=', $dateTo));
    }

    // ─── Business Logic ────────────────────────────────────────────────

    public static function generateBillingNumber(string $billingDate): string
    {
        $ym = \Carbon\Carbon::parse($billingDate)->format('Ym');
        $prefix = 'INV-'.$ym.'-';
        $last = self::withoutGlobalScopes()
            ->where('billing_number', 'like', $prefix.'%')
            ->orderByDesc('billing_number')
            ->value('billing_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
