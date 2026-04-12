<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private string $search,
        private string $status,
        private string $sort,
        private string $direction,
    ) {}

    public function collection()
    {
        $allowedSorts = ['payment_number', 'payment_date', 'subject', 'status', 'total_amount', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'payment_date';
        $sortDir   = $this->direction === 'asc' ? 'asc' : 'desc';

        $payments = Payment::with(['customer:id,code,name', 'employee:id,name', 'items'])
            ->active()
            ->filtered($this->search, $this->status)
            ->orderBy($sortField, $sortDir)
            ->get();

        $rows = collect();

        foreach ($payments as $payment) {
            $header = [
                $payment->payment_number,
                $payment->customer?->code ?? '',
                $payment->customer?->name ?? '',
                $payment->employee?->name ?? '',
                $payment->payment_date?->format('Y/m/d') ?? '',
                $payment->subject,
                Payment::STATUSES[$payment->status] ?? $payment->status,
                $payment->remarks ?? '',
                $payment->total_amount,
                $payment->created_at?->format('Y-m-d H:i:s') ?? '',
                $payment->updated_at?->format('Y-m-d H:i:s') ?? '',
            ];

            if ($payment->items->isEmpty()) {
                $rows->push(array_merge($header, ['', '', '', '', '']));
            } else {
                foreach ($payment->items as $item) {
                    $rows->push(array_merge($header, [
                        $item->line_no,
                        $item->payment_type ?? '',
                        $item->amount,
                        $item->bank_info ?? '',
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
            '入金番号', '得意先コード', '得意先名', '担当者',
            '入金日', '件名', 'ステータス', '備考', '合計入金額',
            '登録日時', '更新日時',
            // 明細
            '行番号', '入金区分', '金額', '銀行情報', '明細備考',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
