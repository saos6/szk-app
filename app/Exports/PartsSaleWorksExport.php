<?php

namespace App\Exports;

use App\Models\PartsSaleWork;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PartsSaleWorksExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private string $processingYm) {}

    public function query()
    {
        return PartsSaleWork::byYm($this->processingYm)
            ->orderBy('sale_date')
            ->orderBy('slip_no');
    }

    public function map($work): array
    {
        return [
            $work->processing_ym,
            $work->monthly_f_kbn ?? '',
            $work->control_code ?? '',
            $work->office_code ?? '',
            $work->hinban ?? '',
            $work->slip_no ?? '',
            $work->order_qty !== null ? (float) $work->order_qty : '',
            $work->order_date?->format('Y/m/d') ?? '',
            $work->order_date_raw ?? '',
            (float) $work->ship_qty,
            $work->sale_date?->format('Y/m/d') ?? '',
            $work->sale_date_raw ?? '',
            (float) $work->unit_price,
            $work->sale_kbn ?? '',
            $work->les_rate ?? '',
            $work->partner_code ?? '',
            $work->dealer_code ?? '',
            (float) $work->cost_price,
            $work->terminal_price ?? '',
            $work->breakdown_code ?? '',
            $work->maintenance_no ?? '',
            $work->red_black_kbn,
            $work->invoice_kbn ?? '',
            $work->invoice_m_kbn ?? '',
            $work->dispatch_source ?? '',
            $work->staff_code ?? '',
            $work->rank_cd ?? '',
            $work->first_ship_kbn ?? '',
            $work->item_code ?? '',
            $work->item_name ?? '',
            $work->open_kbn ?? '',
            $work->standard_retail_price ?? '',
            $work->model_group ?? '',
            $work->filler ?? '',
            (float) $work->quantity,
            $work->model_kisyu_cd ?? '',
            $work->vehicle_kisyu_cd ?? '',
            $work->check_flag === PartsSaleWork::CHECK_NORMAL ? '正常' : 'エラー',
            $work->check_message ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            '処理年月', '月次区分', '制御コード', '営業所CD', '品番', '伝票NO',
            '受注数', '受注日', '受注日(RAW)', '出荷数', '売上日', '売上日(RAW)',
            '販売単価', '売上区分', 'LES比率', '販売店CD(半角)', '販売店CD(全角)',
            '売上原価', '端末価格', '内訳CD', '整備注文NO', '赤黒区分',
            '請求区分', '請求月区分', '出庫元', '担当CD', 'ランクCD',
            '初回区分', '品目CD', '品名', '公開区分', '標準小売価格',
            'モデルグループ', 'フィラー', '数量(変換後)', '機種CD', '車体機種CD',
            'チェック結果', 'エラーメッセージ',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
