<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehiclesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'kisyu_cd',
        private string $direction = 'asc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['id', 'kisyu_cd', 'frame_no', 'kisyu_nm', 'vehicle_no', 'owner_name', 'shop_name', 'sale_date', 'first_reg_date', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'kisyu_cd';
        $sortDir = $this->direction === 'desc' ? 'desc' : 'asc';

        return Vehicle::active()
            ->filtered($this->search)
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', '機種コード', 'フレームNo', '商品名1', '商品名2',
            '機種名', '形式', '機種番号', '色コード',
            '仕入単価(税抜)', '売上単価(税抜)', 'メーカー品番', '単位',
            '特記事項1', '特記事項2', '特記事項3',
            '初年度登録日', '2回目登録日', '車両番号',
            '氏名漢字', '氏名カナ', '生年月日', '郵便番号', '性別',
            '住所1', '住所2', '連絡先', '携帯電話',
            'G防犯登録有無', 'G防犯加入日',
            '盗難保険有無', '盗難保険加入日',
            '保証書登録票有無', '申請書有無', 'DM発送有無',
            '備考', '販売店名', '売上日',
            '作成日時', '更新日時',
        ];
    }

    public function map($v): array
    {
        return [
            $v->id,
            $v->kisyu_cd,
            $v->frame_no,
            $v->name1 ?? '',
            $v->name2 ?? '',
            $v->kisyu_nm ?? '',
            $v->keishiki ?? '',
            $v->kisyu_no ?? '',
            $v->iro_cd ?? '',
            $v->sre_tan !== null ? number_format($v->sre_tan, 2) : '',
            $v->uri_tan !== null ? number_format($v->uri_tan, 2) : '',
            $v->maker_code ?? '',
            $v->unit ?? '',
            $v->note1 ?? '',
            $v->note2 ?? '',
            $v->note3 ?? '',
            $v->first_reg_date?->format('Y-m-d') ?? '',
            $v->second_reg_date?->format('Y-m-d') ?? '',
            $v->vehicle_no ?? '',
            $v->owner_name ?? '',
            $v->owner_kana ?? '',
            $v->birth_date?->format('Y-m-d') ?? '',
            $v->zip_code ?? '',
            Vehicle::GENDERS[$v->gender] ?? ($v->gender ?? ''),
            $v->address1 ?? '',
            $v->address2 ?? '',
            $v->tel ?? '',
            $v->mobile ?? '',
            $v->has_security_reg ? '有' : '無',
            $v->security_reg_date?->format('Y-m-d') ?? '',
            $v->has_theft_insurance ? '有' : '無',
            $v->theft_insurance_date?->format('Y-m-d') ?? '',
            $v->has_warranty ? '有' : '無',
            $v->has_application ? '有' : '無',
            $v->has_dm ? '有' : '無',
            $v->remarks ?? '',
            $v->shop_name ?? '',
            $v->sale_date?->format('Y-m-d') ?? '',
            $v->created_at?->format('Y-m-d H:i:s') ?? '',
            $v->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
