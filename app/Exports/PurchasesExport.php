<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchasesExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private string $search,
        private string $status,
        private string $sort,
        private string $direction,
    ) {}

    public function collection()
    {
        $allowedSorts = ['purchase_number', 'purchase_date', 'subject', 'status', 'subtotal', 'tax_amount', 'total_amount', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'purchase_date';
        $sortDir   = $this->direction === 'asc' ? 'asc' : 'desc';

        $purchases = Purchase::with(['supplier:id,code,name', 'employee:id,name', 'items'])
            ->active()
            ->filtered($this->search, $this->status)
            ->orderBy($sortField, $sortDir)
            ->get();

        $rows = collect();

        foreach ($purchases as $purchase) {
            $header = [
                $purchase->purchase_number,
                $purchase->supplier?->code ?? '',
                $purchase->supplier?->name ?? '',
                $purchase->employee?->name ?? '',
                $purchase->purchase_date?->format('Y/m/d') ?? '',
                $purchase->subject,
                Purchase::STATUSES[$purchase->status] ?? $purchase->status,
                $purchase->remarks ?? '',
                $purchase->subtotal,
                $purchase->tax_amount,
                $purchase->total_amount,
                $purchase->created_at?->format('Y-m-d H:i:s') ?? '',
                $purchase->updated_at?->format('Y-m-d H:i:s') ?? '',
            ];

            if ($purchase->items->isEmpty()) {
                $rows->push(array_merge($header, ['', '', '', '', '', '', '', '', '', '', '', '']));
            } else {
                foreach ($purchase->items as $item) {
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
                        $item->tax_rate,
                        $item->purchase_amount,
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
            '仕入番号', '仕入先コード', '仕入先名', '担当者',
            '仕入日', '件名', 'ステータス', '備考',
            '仕入小計', '消費税', '税込合計',
            '登録日時', '更新日時',
            // 明細
            '行番号', '機種コード(商品)', 'フレームNo(品番)', '倉庫コード', '色コード', '機種名',
            '数量', '単位', '仕入単価', '税率', '仕入金額', '明細備考',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
