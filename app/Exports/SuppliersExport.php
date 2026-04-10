<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SuppliersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'code',
        private string $direction = 'asc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['id', 'code', 'name', 'name_kana', 'phone', 'contact_person', 'payment_site', 'created_at', 'updated_at'];
        $sortField    = in_array($this->sort, $allowedSorts) ? $this->sort : 'code';
        $sortDir      = $this->direction === 'desc' ? 'desc' : 'asc';

        return Supplier::active()
            ->filtered($this->search)
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', '仕入先コード', '仕入先名', '仕入先名カナ',
            '郵便番号', '住所', '電話番号', 'FAX', 'メールアドレス',
            '担当者名', '支払サイト（日）', '備考',
            '作成日時', '更新日時',
        ];
    }

    public function map($supplier): array
    {
        return [
            $supplier->id,
            $supplier->code,
            $supplier->name,
            $supplier->name_kana ?? '',
            $supplier->postal_code ?? '',
            $supplier->address ?? '',
            $supplier->phone ?? '',
            $supplier->fax ?? '',
            $supplier->email ?? '',
            $supplier->contact_person ?? '',
            $supplier->payment_site !== null ? $supplier->payment_site : '',
            $supplier->remarks ?? '',
            $supplier->created_at?->format('Y-m-d H:i:s') ?? '',
            $supplier->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
