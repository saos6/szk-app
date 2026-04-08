<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search,
        private string $status,
        private string $sort,
        private string $direction,
    ) {}

    public function query()
    {
        return Sale::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->filtered($this->search, $this->status)
            ->orderBy($this->sort, $this->direction);
    }

    public function headings(): array
    {
        return ['売上番号', '得意先', '担当者', '売上日', '納品日', '件名', 'ステータス', '売上小計', '消費税', '税込合計', '仕入合計'];
    }

    public function map($sale): array
    {
        return [
            $sale->sale_number,
            $sale->customer?->name ?? '',
            $sale->employee?->name ?? '',
            $sale->sale_date?->format('Y/m/d') ?? '',
            $sale->delivery_date?->format('Y/m/d') ?? '',
            $sale->subject,
            Sale::STATUSES[$sale->status] ?? $sale->status,
            $sale->subtotal,
            $sale->tax_amount,
            $sale->total_amount,
            $sale->cogs_total,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
