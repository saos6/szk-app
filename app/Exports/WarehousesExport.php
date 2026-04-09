<?php

namespace App\Exports;

use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehousesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'code',
        private string $direction = 'asc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['code', 'name', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'code';
        $sortDir = $this->direction === 'desc' ? 'desc' : 'asc';

        return Warehouse::active()
            ->filtered($this->search)
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return ['倉庫コード', '倉庫名', '作成日時', '更新日時'];
    }

    public function map($warehouse): array
    {
        return [
            $warehouse->code,
            $warehouse->name,
            $warehouse->created_at?->format('Y-m-d H:i:s') ?? '',
            $warehouse->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
