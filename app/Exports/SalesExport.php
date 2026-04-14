<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private string $search,
        private string $status,
        private string $sort,
        private string $direction,
    ) {}

    public function collection()
    {
        $allowedSorts = ['sale_number', 'sale_date', 'delivery_date', 'subject', 'status', 'subtotal', 'tax_amount', 'total_amount', 'cogs_total', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'sale_date';
        $sortDir   = $this->direction === 'asc' ? 'asc' : 'desc';

        $sales = Sale::with(['customer:id,code,name', 'employee:id,name', 'items'])
            ->active()
            ->filtered($this->search, $this->status)
            ->orderBy($sortField, $sortDir)
            ->get();

        $rows = collect();

        foreach ($sales as $sale) {
            $header = [
                $sale->sale_number,
                $sale->customer?->code ?? '',
                $sale->customer?->name ?? '',
                $sale->employee?->name ?? '',
                $sale->sale_date?->format('Y/m/d') ?? '',
                $sale->delivery_date?->format('Y/m/d') ?? '',
                $sale->subject,
                Sale::STATUSES[$sale->status] ?? $sale->status,
                $sale->remarks ?? '',
                $sale->subtotal,
                $sale->tax_amount,
                $sale->total_amount,
                $sale->cogs_total,
                $sale->created_at?->format('Y-m-d H:i:s') ?? '',
                $sale->updated_at?->format('Y-m-d H:i:s') ?? '',
            ];

            if ($sale->items->isEmpty()) {
                $rows->push(array_merge($header, ['', '', '', '', '', '', '', '', '', '', '', '', '', '']));
            } else {
                foreach ($sale->items as $item) {
                    $rows->push(array_merge($header, [
                        $item->line_no,
                        $item->model_code ?? '',
                        $item->frame_number ?? '',
                        $item->warehouse_code ?? '',
                        $item->color_code ?? '',
                        $item->model_name ?? '',
                        $item->quantity,
                        $item->unit ?? '',
                        $item->purchase_price,
                        $item->selling_price,
                        $item->tax_rate,
                        $item->sale_amount,
                        $item->cogs_amount,
                        $item->remarks ?? '',
                    ]));
                }
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            // ヘッダ
            '売上番号', '得意先コード', '得意先名', '担当者',
            '売上日', '納品日', '件名', 'ステータス', '備考',
            '売上小計', '消費税', '税込合計', '仕入合計',
            '登録日時', '更新日時',
            // 明細
            '行番号', '機種コード(商品)', 'フレームNo(品番)', '倉庫コード', '色コード', '機種名',
            '数量', '単位', '仕入単価', '売上単価', '税率', '売上金額', '仕入金額', '明細備考',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
