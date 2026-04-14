<?php

namespace App\Exports;

use App\Models\VehicleModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleModelsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'model_code',
        private string $direction = 'asc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['id', 'model_code', 'color_code', 'model_name', 'model_abbr', 'model_name_kanji', 'base_model', 'purchase_price', 'selling_price', 'g1', 'g2', 'tax_type', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'model_code';
        $sortDir = $this->direction === 'desc' ? 'desc' : 'asc';

        return VehicleModel::active()
            ->filtered($this->search)
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', '機種コード', '色コード', '営業機種記号', '機種略称', '基本機種',
            '機種名(漢字)', '仕入単価(税抜)', '売上単価(税抜)',
            'タイプ区分(G1)', '排気量区分(G2)', 'G3', 'G4', 'G5',
            'オーダーNo', '税区分', '作成日時', '更新日時',
        ];
    }

    public function map($model): array
    {
        return [
            $model->id,
            $model->model_code,
            $model->color_code,
            $model->model_name ?? '',
            $model->model_abbr ?? '',
            $model->base_model ?? '',
            $model->model_name_kanji ?? '',
            $model->purchase_price !== null ? number_format($model->purchase_price, 2) : '',
            $model->selling_price !== null ? number_format($model->selling_price, 2) : '',
            VehicleModel::G1_TYPES[$model->g1] ?? ($model->g1 ?? ''),
            VehicleModel::G2_DISP[$model->g2] ?? ($model->g2 ?? ''),
            VehicleModel::G3_OPTIONS[$model->g3] ?? ($model->g3 ?? ''),
            VehicleModel::G4_OPTIONS[$model->g4] ?? ($model->g4 ?? ''),
            VehicleModel::G5_OPTIONS[$model->g5] ?? ($model->g5 ?? ''),
            $model->order_number ?? '',
            VehicleModel::ZEI_KBN[$model->tax_type] ?? ($model->tax_type ?? ''),
            $model->created_at?->format('Y-m-d H:i:s') ?? '',
            $model->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
