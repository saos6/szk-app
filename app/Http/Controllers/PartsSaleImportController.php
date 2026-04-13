<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PartsSaleWork;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SystemSetting;
use App\Models\VehicleModel;
use App\Models\Vehicle;
use App\Exports\PartsSaleWorksExport;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PartsSaleImportController extends Controller
{
    // CSV ヘッダー期待値（31カラム）
    private const CSV_HEADERS = [
        '月報Ｆ登録区分', 'ｺﾝﾄﾛｰﾙｺｰﾄﾞ', '品番', '営業所ｺｰﾄﾞ', '伝票ＮＯ',
        '受注数', '受注日', '出荷数', '売上日', '販売単価',
        '販売区分', 'ﾚｽ率', '販売店ｺｰﾄﾞ', '売上原価', '末端価格',
        '内訳ｺｰﾄﾞ', '整備注文ＮＯ', '赤黒区分', '請求書発行区分', '請求書Ｍ登録区分',
        '出庫元', '担当', 'ﾗﾝｸ', '初回出荷区分', '品目ｺｰﾄﾞ',
        '品名', 'オープン区分', '販売店コード', '標準小売価格', '機種グループ', 'FILLER',
    ];
    private const CSV_COLUMN_COUNT = 31;

    // ────────────────────────────────────────────────
    // 画面表示
    // ────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $ym      = $request->input('processing_ym', now()->format('Y-m'));
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 50;
        $search  = $request->get('search', '');

        $allowedSorts = [
            'processing_ym', 'hinban', 'slip_no', 'order_date', 'sale_date',
            'ship_qty', 'unit_price', 'cost_price', 'partner_code', 'item_name',
            'quantity', 'check_flag',
        ];
        $sort      = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'sale_date';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        // サマリーは検索フィルタに関係なく処理年月全体で集計
        $total  = PartsSaleWork::byYm($ym)->count();
        $errors = PartsSaleWork::byYm($ym)->where('check_flag', PartsSaleWork::CHECK_ERROR)->count();

        $works = PartsSaleWork::byYm($ym)
            ->filtered($search)
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('PartsSaleImport/Index', [
            'works'        => $works,
            'processingYm' => $ym,
            'summary'      => ['total' => $total, 'errors' => $errors, 'ok' => $total - $errors],
            'filters'      => [
                'per_page'  => (string) $perPage,
                'search'    => $search,
                'sort'      => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    // ────────────────────────────────────────────────
    // CSV 取込
    // ────────────────────────────────────────────────

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file'      => ['required', 'file'],
            'processing_ym' => ['required', 'date_format:Y-m'],
        ]);

        $ym   = $request->input('processing_ym');
        $file = $request->file('csv_file');

        // Shift-JIS → UTF-8 変換
        $raw = file_get_contents($file->getRealPath());
        $utf = mb_convert_encoding($raw, 'UTF-8', 'SJIS-win');

        // 行分割
        $lines = preg_split('/\r\n|\r|\n/', trim($utf));
        if (count($lines) < 2) {
            return back()->with('error', 'CSVにデータ行がありません。');
        }

        // ヘッダーチェック
        $headerRow = str_getcsv($lines[0]);
        if (count($headerRow) !== self::CSV_COLUMN_COUNT) {
            return back()->with('error', sprintf(
                'CSVのカラム数が不正です（期待値: %d、実際: %d）。',
                self::CSV_COLUMN_COUNT,
                count($headerRow)
            ));
        }
        // 主要ヘッダー名チェック（品番・売上日・販売店コード）
        $checkIdxs = [2 => '品番', 8 => '売上日', 12 => '販売店ｺｰﾄﾞ'];
        foreach ($checkIdxs as $idx => $expected) {
            if (($headerRow[$idx] ?? '') !== $expected) {
                return back()->with('error', sprintf(
                    'CSVヘッダーが不正です（列%d: 期待「%s」、実際「%s」）。',
                    $idx + 1, $expected, $headerRow[$idx] ?? ''
                ));
            }
        }

        // データ行パース
        $rows = [];
        $now  = now();

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') continue;

            $cols = str_getcsv($line);
            if (count($cols) < self::CSV_COLUMN_COUNT) continue;

            $hinban       = trim($cols[2]);
            $saleDateRaw  = trim($cols[8]);
            $redBlackKbn  = trim($cols[17]);
            $shipQty      = (float) trim($cols[7]);
            $quantity     = $redBlackKbn === '2' ? $shipQty * -1 : $shipQty;
            $modelKisyuCd = mb_strlen($hinban) >= 5 ? mb_substr($hinban, 0, 5) : null;
            $vehicleKisyuCd = mb_strlen($hinban) >= 10 ? mb_substr($hinban, 5, 5) : null;

            $rows[] = [
                'processing_ym'          => $ym,
                'monthly_f_kbn'          => trim($cols[0]) ?: null,
                'control_code'           => trim($cols[1]),
                'hinban'                 => $hinban,
                'office_code'            => trim($cols[3]) ?: null,
                'slip_no'                => trim($cols[4]),
                'order_qty'              => (float) trim($cols[5]),
                'order_date_raw'         => trim($cols[6]),
                'order_date'             => $this->parseReiwaDate(trim($cols[6])),
                'ship_qty'               => $shipQty,
                'sale_date_raw'          => $saleDateRaw,
                'sale_date'              => $this->parseReiwaDate($saleDateRaw),
                'unit_price'             => (float) trim($cols[9]),
                'sale_kbn'               => trim($cols[10]) ?: null,
                'les_rate'               => trim($cols[11]) ?: null,
                'partner_code'           => trim($cols[12]),
                'cost_price'             => (float) trim($cols[13]),
                'terminal_price'         => trim($cols[14]) ?: null,
                'breakdown_code'         => trim($cols[15]) ?: null,
                'maintenance_no'         => trim($cols[16]) ?: null,
                'red_black_kbn'          => $redBlackKbn,
                'invoice_kbn'            => trim($cols[18]) ?: null,
                'invoice_m_kbn'          => trim($cols[19]) ?: null,
                'dispatch_source'        => trim($cols[20]) ?: null,
                'staff_code'             => trim($cols[21]) ?: null,
                'rank_cd'                => trim($cols[22]) ?: null,
                'first_ship_kbn'         => trim($cols[23]) ?: null,
                'item_code'              => trim($cols[24]) ?: null,
                'item_name'              => trim($cols[25]) ?: null,
                'open_kbn'               => trim($cols[26]) ?: null,
                'dealer_code'            => trim($cols[27]) ?: null,
                'standard_retail_price'  => trim($cols[28]) ?: null,
                'model_group'            => trim($cols[29]) ?: null,
                'filler'                 => trim($cols[30]) ?: null,
                'quantity'               => $quantity,
                'model_kisyu_cd'         => $modelKisyuCd,
                'vehicle_kisyu_cd'       => $vehicleKisyuCd,
                'check_flag'             => PartsSaleWork::CHECK_NORMAL,
                'check_message'          => null,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];
        }

        if (empty($rows)) {
            return back()->with('error', '取込可能なデータ行がありませんでした。');
        }

        // 処理年月で既存レコードを削除してから一括挿入
        PartsSaleWork::where('processing_ym', $ym)->delete();
        foreach (array_chunk($rows, 500) as $chunk) {
            PartsSaleWork::insert($chunk);
        }

        // 挿入後にチェックを実行
        $insertedWorks = PartsSaleWork::byYm($ym)->orderBy('id')->get();
        $errorCount    = $this->runChecks($ym, $insertedWorks);

        $totalCount = count($rows);
        if ($errorCount > 0) {
            return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
                ->with('error', sprintf(
                    '%d件のデータを取込みました。うち%d件にチェックエラーがあります。エラー行を確認・修正してから売上変換を実行してください。',
                    $totalCount,
                    $errorCount
                ));
        }

        return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
            ->with('success', sprintf('%d件のデータを取込みました。全件正常です。', $totalCount));
    }

    // ────────────────────────────────────────────────
    // ワーク CRUD
    // ────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWorkRow($request);
        PartsSaleWork::create($validated);

        return redirect()->route('parts-sale-import.index', ['processing_ym' => $validated['processing_ym']])
            ->with('success', '行を追加しました。');
    }

    public function update(Request $request, PartsSaleWork $partsSaleWork): RedirectResponse
    {
        $validated = $this->validateWorkRow($request);
        $partsSaleWork->update($validated + ['check_flag' => PartsSaleWork::CHECK_NORMAL, 'check_message' => null]);

        return redirect()->route('parts-sale-import.index', ['processing_ym' => $partsSaleWork->processing_ym])
            ->with('success', '行を更新しました。');
    }

    public function destroy(PartsSaleWork $partsSaleWork): RedirectResponse
    {
        $ym = $partsSaleWork->processing_ym;
        $partsSaleWork->delete();

        return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
            ->with('success', '行を削除しました。');
    }

    // ────────────────────────────────────────────────
    // 照合（チェックのみ実行）
    // ────────────────────────────────────────────────

    public function check(Request $request): RedirectResponse
    {
        $request->validate(['processing_ym' => ['required', 'date_format:Y-m']]);
        $ym = $request->input('processing_ym');

        $works = PartsSaleWork::byYm($ym)->orderBy('id')->get();
        if ($works->isEmpty()) {
            return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
                ->with('error', '対象データがありません。先に取込を実行してください。');
        }

        $errorCount = $this->runChecks($ym, $works);
        $total      = $works->count();

        if ($errorCount > 0) {
            return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
                ->with('error', sprintf('照合完了。%d件中%d件にエラーがあります。エラー行を確認・修正してください。', $total, $errorCount));
        }

        return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
            ->with('success', sprintf('照合完了。%d件全件正常です。', $total));
    }

    // ────────────────────────────────────────────────
    // 一括削除
    // ────────────────────────────────────────────────

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate(['processing_ym' => ['required', 'date_format:Y-m']]);
        $ym    = $request->input('processing_ym');
        $count = PartsSaleWork::where('processing_ym', $ym)->count();

        if ($count === 0) {
            return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
                ->with('error', '削除対象のデータがありません。');
        }

        PartsSaleWork::where('processing_ym', $ym)->delete();

        return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
            ->with('success', sprintf('%s のデータ %d件を一括削除しました。', $ym, $count));
    }

    // ────────────────────────────────────────────────
    // Excel出力
    // ────────────────────────────────────────────────

    public function export(Request $request): BinaryFileResponse
    {
        $request->validate(['processing_ym' => ['required', 'date_format:Y-m']]);
        $ym       = $request->input('processing_ym');
        $filename = 'parts_sale_works_' . str_replace('-', '', $ym) . '.xlsx';

        return Excel::download(new PartsSaleWorksExport($ym), $filename);
    }

    // ────────────────────────────────────────────────
    // 売上変換処理
    // ────────────────────────────────────────────────

    public function convert(Request $request): RedirectResponse
    {
        $request->validate(['processing_ym' => ['required', 'date_format:Y-m']]);
        $ym = $request->input('processing_ym');

        $works = PartsSaleWork::byYm($ym)->orderBy('id')->get();
        if ($works->isEmpty()) {
            return back()->with('error', '対象データがありません。先に取込を実行してください。');
        }

        // ── バリデーション（チェック実行） ──
        $errorCount = $this->runChecks($ym, $works);

        if ($errorCount > 0) {
            return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
                ->with('error', 'バリデーションエラーがあります。一覧のエラー行を確認・修正してから再実行してください。');
        }

        // ── 売上変換（伝票NO単位にグループ化して1伝票N明細で作成） ──
        // slip_no → works のグループに分割（orderBy slip_no, id で行順を保持）
        $groups = $works->groupBy('slip_no');
        $count  = 0;

        try {
            DB::transaction(function () use ($groups, $ym, &$count) {
                foreach ($groups as $slipNo => $slipWorks) {
                    // グループ内の先頭行からヘッダー情報を取得
                    $first    = $slipWorks->first();
                    $customer = Customer::active()->where('partner_code', $first->partner_code)->first();
                    $saleDate = $first->sale_date->format('Y-m-d');

                    // 明細行ごとの金額を計算し、ヘッダー合計も積算
                    $items       = [];
                    $subtotal    = 0.0;
                    $cogsTotal   = 0.0;
                    $lineNo      = 1;

                    foreach ($slipWorks as $work) {
                        $qty              = (float) $work->quantity;
                        $uriTan           = (float) $work->unit_price;
                        $sreTan           = (float) $work->cost_price;
                        $terminalPrice    = ($work->terminal_price !== null && $work->terminal_price !== '')
                                               ? (float) $work->terminal_price : null;
                        $stdRetailPrice   = ($work->standard_retail_price !== null && $work->standard_retail_price !== '')
                                               ? (float) $work->standard_retail_price : null;
                        $saleAmt          = round($qty * $uriTan, 2);
                        $cogsAmt          = round($qty * $sreTan, 2);

                        $items[] = [
                            'line_no'        => $lineNo++,
                            'vehicle_id'     => null,
                            'kisyu_cd'       => $work->model_kisyu_cd,
                            'frame_no'       => $work->vehicle_kisyu_cd,
                            'warehouse_code' => $work->dispatch_source,
                            'iro_cd'         => null,
                            'kisyu_nm'       => $work->item_name,
                            'quantity'       => $qty,
                            'unit'           => '個',
                            'sre_tan'        => $sreTan,
                            'uri_tan'        => $uriTan,
                            'terminal_price' => $terminalPrice,
                            'tax_rate'       => 10,
                            'sale_amount'    => $saleAmt,
                            'cogs_amount'    => $cogsAmt,
                            'remarks'        => $work->maintenance_no,
                        ];

                        // 車両（品番）マスタの単価を上書き更新
                        if ($work->vehicle_kisyu_cd) {
                            Vehicle::where('frame_no', $work->vehicle_kisyu_cd)
                                ->where('is_deleted', false)
                                ->update([
                                    'sre_tan'              => $sreTan ?: null,
                                    'uri_tan'              => $uriTan ?: null,
                                    'terminal_price'        => $terminalPrice,
                                    'standard_retail_price' => $stdRetailPrice,
                                ]);
                        }

                        // 車両機種（商品）マスタの単価を上書き更新
                        if ($work->model_kisyu_cd) {
                            VehicleModel::where('kisyu_cd', $work->model_kisyu_cd)
                                ->where('is_deleted', false)
                                ->update([
                                    'sre_tan'              => $sreTan ?: null,
                                    'uri_tan'              => $uriTan ?: null,
                                    'terminal_price'        => $terminalPrice,
                                    'standard_retail_price' => $stdRetailPrice,
                                ]);
                        }

                        $subtotal  += $saleAmt;
                        $cogsTotal += $cogsAmt;
                    }

                    $taxAmount   = round($subtotal * 10 / 100, 2);
                    $totalAmount = round($subtotal + $taxAmount, 2);

                    // 売上ヘッダー作成（伝票NO単位で1件）
                    $sale = Sale::create([
                        'sale_number'     => Sale::generateSaleNumber(),
                        'import_no'       => $slipNo,
                        'partner_slip_no' => $slipNo,
                        'customer_id'     => $customer->id,
                        'employee_id'     => null,
                        'sale_date'       => $saleDate,
                        'order_date'      => $first->order_date?->format('Y-m-d'),
                        'subject'         => '部品売上',
                        'status'          => 'recorded',
                        'subtotal'        => $subtotal,
                        'tax_amount'      => $taxAmount,
                        'total_amount'    => $totalAmount,
                        'cogs_total'      => $cogsTotal,
                        'remarks'         => null,
                    ]);

                    // 売上明細を一括作成・在庫更新
                    foreach ($items as &$item) {
                        $item['sale_id'] = $sale->id;
                        SaleItem::create($item);
                    }
                    unset($item);

                    InventoryService::applyItems($ym, $items, 'out');

                    $count++;
                }
            });
        } catch (\Throwable $e) {
            return redirect()->route('parts-sale-import.index', ['processing_ym' => $ym])
                ->with('error', '変換中にエラーが発生しました: ' . $e->getMessage());
        }

        return redirect()->route('sales.index')
            ->with('success', "{$count}伝票（部品売上）を売上データに変換しました。");
    }

    // ────────────────────────────────────────────────
    // Private helpers
    // ────────────────────────────────────────────────

    /**
     * 令和日付文字列（EYYMMD D: E=元号コード3=令和, YY=年, MM=月, DD=日）を
     * YYYY-MM-DD 形式に変換する。変換不可の場合は null を返す。
     */
    private function parseReiwaDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (strlen($raw) !== 6) return null;

        $era   = (int) $raw[0];
        $year  = (int) $raw[1];
        $month = (int) substr($raw, 2, 2);
        $day   = (int) substr($raw, 4, 2);

        if ($era === 3) {           // 令和（令和1=2019年）
            $westernYear = 2018 + $year;
        } elseif ($era === 4) {     // 平成（後方互換）
            $westernYear = 1988 + $year;
        } else {
            return null;
        }

        if (! checkdate($month, $day, $westernYear)) return null;

        return sprintf('%04d-%02d-%02d', $westernYear, $month, $day);
    }

    /**
     * ワーク行一覧に対してビジネスルールチェックを実行し、
     * check_flag / check_message を更新する。エラー件数を返す。
     *
     * マスタを一括取得してメモリ照合するため、大量行でも高速。
     */
    private function runChecks(string $ym, \Illuminate\Support\Collection $works): int
    {
        if ($works->isEmpty()) return 0;

        $closingYm = SystemSetting::instance()->closing_ym;

        // マスタを一括取得してハッシュセット化（行ごとのクエリを排除）
        $validPartnerCodes = array_flip(
            Customer::active()->whereNotNull('partner_code')->pluck('partner_code')->toArray()
        );
        $validModelCodes = array_flip(
            VehicleModel::active()->pluck('kisyu_cd')->toArray()
        );
        $validVehicleCodes = array_flip(
            Vehicle::active()->pluck('kisyu_cd')->toArray()
        );

        // 既変換の伝票NOセットを一括取得（伝票NO単位で1売上を作るため伝票NOのみで重複判定）
        $slipNos = $works->pluck('slip_no')->filter()->unique()->toArray();
        $existingKeys = [];
        if (! empty($slipNos)) {
            $existingKeys = array_flip(
                Sale::whereIn('import_no', $slipNos)
                    ->where('is_deleted', false)
                    ->pluck('import_no')
                    ->toArray()
            );
        }

        $errorCount = 0;

        DB::transaction(function () use (
            $works, $ym, $closingYm,
            $validPartnerCodes, $validModelCodes, &$validVehicleCodes, $existingKeys,
            &$errorCount
        ) {
            foreach ($works as $work) {
                $messages = [];

                // 1. 処理年月と売上日年月のアンマッチチェック
                if (! $work->sale_date || Carbon::parse($work->sale_date)->format('Y-m') !== $ym) {
                    $messages[] = '売上日の年月が処理年月と一致しません';
                }

                // 2. 月次更新済み期間チェック
                if ($closingYm && $work->sale_date && Carbon::parse($work->sale_date)->format('Y-m') <= $closingYm) {
                    $messages[] = '月次更新済みの期間（' . Carbon::parse($work->sale_date)->format('Y年n月') . '）です';
                }

                // 3. 得意先存在チェック（販売店コード → 相手先コード）
                if (! $work->partner_code || ! array_key_exists($work->partner_code, $validPartnerCodes)) {
                    $messages[] = "販売店コード[{$work->partner_code}]の得意先が見つかりません";
                }

                // 4. 車両機種（商品）マスタ存在チェック（品番1-5桁）― 存在しない場合はエラー
                if (! $work->model_kisyu_cd || ! array_key_exists($work->model_kisyu_cd, $validModelCodes)) {
                    $messages[] = "品番先頭5桁[{$work->model_kisyu_cd}]が車両機種マスタに存在しません";
                }

                // 5. 車両（品番）マスタ自動作成（品番6-10桁）― 存在しない場合は自動作成しエラーにしない
                if (! $work->vehicle_kisyu_cd) {
                    $messages[] = '品番が10桁未満のため車両コードを取得できません';
                } elseif (! array_key_exists($work->vehicle_kisyu_cd, $validVehicleCodes)) {
                    Vehicle::create([
                        'kisyu_cd'             => $work->vehicle_kisyu_cd,
                        'frame_no'             => $work->vehicle_kisyu_cd,
                        'kisyu_nm'             => $work->item_name,
                        'sre_tan'              => ($work->cost_price !== null && (float) $work->cost_price > 0)
                                                     ? (float) $work->cost_price : null,
                        'uri_tan'              => ($work->unit_price !== null && (float) $work->unit_price > 0)
                                                     ? (float) $work->unit_price : null,
                        'terminal_price'        => ($work->terminal_price !== null && $work->terminal_price !== '')
                                                     ? (float) $work->terminal_price : null,
                        'standard_retail_price' => ($work->standard_retail_price !== null && $work->standard_retail_price !== '')
                                                     ? (float) $work->standard_retail_price : null,
                    ]);
                    // 同一コードの重複作成を防ぐためキャッシュに追加
                    $validVehicleCodes[$work->vehicle_kisyu_cd] = true;
                }

                // 6. 重複チェック（伝票NO単位）
                if ($work->slip_no && isset($existingKeys[$work->slip_no])) {
                    $messages[] = "伝票NO[{$work->slip_no}]は既に売上に変換済みです";
                }

                if (empty($messages)) {
                    $work->check_flag    = PartsSaleWork::CHECK_NORMAL;
                    $work->check_message = null;
                } else {
                    $work->check_flag    = PartsSaleWork::CHECK_ERROR;
                    $work->check_message = implode(' / ', $messages);
                    $errorCount++;
                }
                $work->save();
            }
        });

        return $errorCount;
    }

    /** ワーク行の登録・更新バリデーション */
    private function validateWorkRow(Request $request): array
    {
        $validated = $request->validate([
            // 基本
            'processing_ym'          => ['required', 'date_format:Y-m'],
            'monthly_f_kbn'          => ['nullable', 'string', 'max:5'],
            'control_code'           => ['nullable', 'string', 'max:5'],
            'office_code'            => ['nullable', 'string', 'max:10'],
            // 伝票・品番
            'hinban'                 => ['required', 'string', 'max:20'],
            'slip_no'                => ['required', 'string', 'max:20'],
            'red_black_kbn'          => ['nullable', 'string', 'in:0,2'],
            // 受注
            'order_qty'              => ['nullable', 'numeric', 'min:0'],
            'order_date'             => ['nullable', 'date'],
            // 売上
            'ship_qty'               => ['required', 'numeric'],
            'sale_date'              => ['required', 'date'],
            'unit_price'             => ['required', 'numeric', 'min:0'],
            'sale_kbn'               => ['nullable', 'string', 'max:5'],
            'les_rate'               => ['nullable', 'string', 'max:10'],
            'cost_price'             => ['nullable', 'numeric', 'min:0'],
            'terminal_price'         => ['nullable', 'string', 'max:20'],
            'breakdown_code'         => ['nullable', 'string', 'max:10'],
            // 得意先
            'partner_code'           => ['required', 'string', 'max:20'],
            'dealer_code'            => ['nullable', 'string', 'max:20'],
            // 請求
            'invoice_kbn'            => ['nullable', 'string', 'max:5'],
            'invoice_m_kbn'          => ['nullable', 'string', 'max:5'],
            // 出庫・担当
            'dispatch_source'        => ['nullable', 'string', 'max:20'],
            'staff_code'             => ['nullable', 'string', 'max:20'],
            'rank_cd'                => ['nullable', 'string', 'max:5'],
            'first_ship_kbn'         => ['nullable', 'string', 'max:5'],
            // 商品
            'item_code'              => ['nullable', 'string', 'max:20'],
            'item_name'              => ['nullable', 'string', 'max:200'],
            'open_kbn'               => ['nullable', 'string', 'max:5'],
            'model_group'            => ['nullable', 'string', 'max:10'],
            'maintenance_no'         => ['nullable', 'string', 'max:100'],
            'standard_retail_price'  => ['nullable', 'string', 'max:20'],
            // その他
            'filler'                 => ['nullable', 'string', 'max:100'],
        ]);

        $hinban           = $validated['hinban'];
        $redBlackKbn      = $validated['red_black_kbn'] ?? '0';
        $shipQty          = (float) ($validated['ship_qty'] ?? 0);

        $validated['quantity']          = $redBlackKbn === '2' ? $shipQty * -1 : $shipQty;
        $validated['model_kisyu_cd']    = mb_strlen($hinban) >= 5 ? mb_substr($hinban, 0, 5) : null;
        $validated['vehicle_kisyu_cd']  = mb_strlen($hinban) >= 10 ? mb_substr($hinban, 5, 5) : null;
        $validated['check_flag']        = PartsSaleWork::CHECK_NORMAL;
        $validated['check_message']     = null;

        return $validated;
    }
}
