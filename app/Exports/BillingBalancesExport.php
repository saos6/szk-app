<?php

namespace App\Exports;

use App\Models\BillingBalance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BillingBalancesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'billing_date',
        private string $direction = 'desc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['billing_date', 'prev_amount', 'sales_amount', 'tax_amount', 'total_amount', 'payment_amount', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'billing_date';
        $sortDir = $this->direction === 'desc' ? 'desc' : 'asc';

        return BillingBalance::with('customer')
            ->active()
            ->filtered($this->search)
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', '請求日', '得意先コード', '得意先名',
            '前月繰越額', '売上金額', '消費税', '税込金額', '入金額',
            '作成日時', '更新日時',
        ];
    }

    public function map($billing): array
    {
        return [
            $billing->id,
            $billing->billing_date?->format('Y-m-d') ?? '',
            $billing->customer?->code ?? '',
            $billing->customer?->name ?? '',
            $billing->prev_amount,
            $billing->sales_amount,
            $billing->tax_amount,
            $billing->total_amount,
            $billing->payment_amount,
            $billing->created_at?->format('Y-m-d H:i:s') ?? '',
            $billing->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
