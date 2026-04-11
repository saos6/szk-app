<?php

namespace App\Exports;

use App\Models\InventoryBalance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryBalancesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $ymFrom = '',
        private string $ymTo = '',
        private string $sort = 'stock_ym',
        private string $direction = 'desc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['stock_ym', 'warehouse_code', 'vehicle_model_code', 'frame_no', 'prev_stock', 'in_stock', 'out_stock', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'stock_ym';
        $sortDir = $this->direction === 'asc' ? 'asc' : 'desc';

        return InventoryBalance::active()
            ->filtered($this->search, $this->ymFrom, $this->ymTo)
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return ['年月', '倉庫コード', '機種コード（商品）', 'フレームNo（品番）', '前月繰越在庫数', '当月入庫数', '当月出庫数', '当月在庫数', '作成日時', '更新日時'];
    }

    public function map($row): array
    {
        return [
            $row->stock_ym,
            $row->warehouse_code,
            $row->vehicle_model_code,
            $row->frame_no,
            $row->prev_stock,
            $row->in_stock,
            $row->out_stock,
            $row->current_stock,
            $row->created_at?->format('Y-m-d H:i:s') ?? '',
            $row->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
