<?php

namespace App\Exports;

use App\Models\Quote;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuotesExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private string $search,
        private string $status,
        private string $sort,
        private string $direction,
    ) {}

    public function collection()
    {
        $allowedSorts = ['quote_number', 'quote_date', 'expiry_date', 'subject', 'status', 'subtotal', 'tax_amount', 'total_amount', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'quote_date';
        $sortDir   = $this->direction === 'asc' ? 'asc' : 'desc';

        $quotes = Quote::with(['customer:id,code,name', 'employee:id,name', 'items.product:id,code'])
            ->active()
            ->filtered($this->search, $this->status)
            ->orderBy($sortField, $sortDir)
            ->get();

        $rows = collect();

        foreach ($quotes as $quote) {
            $header = [
                $quote->quote_number,
                $quote->customer?->code ?? '',
                $quote->customer?->name ?? '',
                $quote->employee?->name ?? '',
                $quote->quote_date?->format('Y/m/d') ?? '',
                $quote->expiry_date?->format('Y/m/d') ?? '',
                $quote->subject,
                Quote::STATUSES[$quote->status] ?? $quote->status,
                $quote->remarks ?? '',
                $quote->subtotal,
                $quote->tax_amount,
                $quote->total_amount,
                $quote->created_at?->format('Y-m-d H:i:s') ?? '',
                $quote->updated_at?->format('Y-m-d H:i:s') ?? '',
            ];

            if ($quote->items->isEmpty()) {
                $rows->push(array_merge($header, ['', '', '', '', '', '', '', '', '', '']));
            } else {
                foreach ($quote->items as $item) {
                    $rows->push(array_merge($header, [
                        $item->line_no,
                        $item->product?->code ?? '',
                        $item->product_name ?? '',
                        $item->spec ?? '',
                        $item->quantity,
                        $item->unit ?? '',
                        $item->unit_price,
                        $item->tax_rate,
                        $item->amount,
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
            '見積番号', '得意先コード', '得意先名', '担当者',
            '見積日', '有効期限', '件名', 'ステータス', '備考',
            '小計', '消費税', '合計',
            '登録日時', '更新日時',
            // 明細
            '行番号', '商品コード', '商品名', '仕様', '数量', '単位', '単価', '税率', '金額', '明細備考',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
