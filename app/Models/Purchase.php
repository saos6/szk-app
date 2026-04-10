<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    public const STATUSES = [
        'draft'     => '下書き',
        'recorded'  => '計上',
        'completed' => '完了',
        'cancelled' => 'キャンセル',
    ];

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'employee_id',
        'purchase_date',
        'subject',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'remarks',
    ];

    protected $casts = [
        'purchase_date' => 'date:Y-m-d',
        'subtotal'      => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'total_amount'  => 'decimal:2',
    ];

    // ─── Relationships ──────────────────────────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class)->orderBy('line_no');
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
                    $q2->where('purchase_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($q3) => $q3->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status));
    }

    // ─── Business Logic ────────────────────────────────────────────────

    public static function generatePurchaseNumber(): string
    {
        $prefix = 'PU-'.now()->format('Ym').'-';
        $last = self::withoutGlobalScopes()
            ->where('purchase_number', 'like', $prefix.'%')
            ->orderByDesc('purchase_number')
            ->value('purchase_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
