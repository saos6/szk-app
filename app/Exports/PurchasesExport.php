<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchasesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search,
        private string $status,
        private string $sort,
        private string $direction,
    ) {}

    public function query()
    {
        return Purchase::active()
            ->with(['supplier:id,name', 'employee:id,name'])
            ->filtered($this->search, $this->status)
            ->orderBy($this->sort, $this->direction);
    }

    public function headings(): array
    {
        return ['仕入番号', '仕入先', '担当者', '仕入日', '件名', 'ステータス', '仕入小計', '消費税', '税込合計'];
    }

    public function map($purchase): array
    {
        return [
            $purchase->purchase_number,
            $purchase->supplier?->name ?? '',
            $purchase->employee?->name ?? '',
            $purchase->purchase_date?->format('Y/m/d') ?? '',
            $purchase->subject,
            Purchase::STATUSES[$purchase->status] ?? $purchase->status,
            $purchase->subtotal,
            $purchase->tax_amount,
            $purchase->total_amount,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
