<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search,
        private string $status,
        private string $sort,
        private string $direction,
    ) {}

    public function query()
    {
        return Payment::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->filtered($this->search, $this->status)
            ->orderBy($this->sort, $this->direction);
    }

    public function headings(): array
    {
        return ['入金番号', '得意先', '担当者', '入金日', '件名', 'ステータス', '合計入金額'];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_number,
            $payment->customer?->name ?? '',
            $payment->employee?->name ?? '',
            $payment->payment_date?->format('Y/m/d') ?? '',
            $payment->subject,
            Payment::STATUSES[$payment->status] ?? $payment->status,
            $payment->total_amount,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
