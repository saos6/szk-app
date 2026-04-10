<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    public const STATUSES = [
        'draft'     => '下書き',
        'recorded'  => '計上',
        'confirmed' => '請求中',
        'completed' => '請求完了',
        'closed'    => '完了',
        'cancelled' => 'キャンセル',
    ];

    public const PAYMENT_TYPES = [
        'cash'     => '現金',
        'transfer' => '振込',
        'fee'      => '手数料',
        'offset'   => '相殺',
        'other'    => 'その他',
    ];

    protected $fillable = [
        'payment_number',
        'payment_date',
        'customer_id',
        'employee_id',
        'subject',
        'status',
        'total_amount',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'total_amount' => 'decimal:2',
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
        return $this->hasMany(PaymentItem::class)->orderBy('line_no');
    }

    public function billingBalance(): BelongsTo
    {
        return $this->belongsTo(BillingBalance::class);
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
                    $q2->where('payment_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($q3) => $q3->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status));
    }

    // ─── Business Logic ────────────────────────────────────────────────

    public static function generatePaymentNumber(): string
    {
        $prefix = 'PM-'.now()->format('Ym').'-';
        $last = self::withoutGlobalScopes()
            ->where('payment_number', 'like', $prefix.'%')
            ->orderByDesc('payment_number')
            ->value('payment_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
