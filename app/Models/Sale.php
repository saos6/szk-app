<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    public const STATUSES = [
        'draft' => '下書き',
        'delivered' => '納品済み',
        'invoiced' => '請求済み',
        'completed' => '完了',
        'cancelled' => 'キャンセル',
    ];

    protected $fillable = [
        'sale_number',
        'customer_id',
        'employee_id',
        'sale_date',
        'delivery_date',
        'subject',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'cogs_total',
        'remarks',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cogs_total' => 'decimal:2',
    ];

    // ─── Relationships ──────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class)->orderBy('line_no');
    }

    // ─── Scopes ────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeFiltered(Builder $query, string $search, string $status = ''): Builder
    {
        return $query
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('sale_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($q3) => $q3->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status));
    }

    // ─── Business Logic ────────────────────────────────────────────────

    public static function generateSaleNumber(): string
    {
        $prefix = 'SA-'.now()->format('Ym').'-';
        $last = self::withoutGlobalScopes()
            ->where('sale_number', 'like', $prefix.'%')
            ->orderByDesc('sale_number')
            ->value('sale_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
