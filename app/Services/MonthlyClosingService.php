<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyClosingService
{
    /**
     * 確定処理
     * - 売上/入金 ステータス '請求完了'(completed) → '完了'(closed)
     * - 仕入 ステータス '計上'(recorded) → '完了'(completed)
     * - 在庫残高 翌月レコードをupsert（前月繰越＝当月前繰越＋入庫－出庫）
     * - システム設定の月次更新年月を更新
     */
    public static function confirm(string $ym): void
    {
        DB::transaction(function () use ($ym) {
            // 売上: 請求完了 → 完了
            Sale::active()
                ->where('sale_date', 'like', $ym . '-%')
                ->where('status', 'completed')
                ->update(['status' => 'closed']);

            // 入金: 請求完了 → 完了
            Payment::active()
                ->where('payment_date', 'like', $ym . '-%')
                ->where('status', 'completed')
                ->update(['status' => 'closed']);

            // 仕入: 計上 → 完了
            Purchase::active()
                ->where('purchase_date', 'like', $ym . '-%')
                ->where('status', 'recorded')
                ->update(['status' => 'completed']);

            // 在庫残高 繰越処理
            $nextYm = Carbon::createFromFormat('Y-m', $ym)->addMonth()->format('Y-m');
            $balances = InventoryBalance::where('stock_ym', $ym)->get();
            foreach ($balances as $balance) {
                $nextStock = $balance->prev_stock + $balance->in_stock - $balance->out_stock;
                InventoryBalance::updateOrCreate(
                    [
                        'stock_ym'           => $nextYm,
                        'warehouse_code'     => $balance->warehouse_code,
                        'model_code'   => $balance->model_code,
                        'frame_number' => $balance->frame_number,
                    ],
                    ['prev_stock' => $nextStock]
                );
            }

            // システム設定更新
            SystemSetting::instance()->update(['closing_ym' => $ym]);
        });
    }

    /**
     * 取消処理
     * - 売上/入金 ステータス '完了'(closed) → '請求完了'(completed)
     * - 仕入 ステータス '完了'(completed) → '計上'(recorded)
     * - 在庫残高 翌月レコードの前月繰越を0にリセット
     * - システム設定の月次更新年月を更新
     */
    public static function cancel(string $ym): void
    {
        DB::transaction(function () use ($ym) {
            // 売上: 完了 → 請求完了
            Sale::active()
                ->where('sale_date', 'like', $ym . '-%')
                ->where('status', 'closed')
                ->update(['status' => 'completed']);

            // 入金: 完了 → 請求完了
            Payment::active()
                ->where('payment_date', 'like', $ym . '-%')
                ->where('status', 'closed')
                ->update(['status' => 'completed']);

            // 仕入: 完了 → 計上
            Purchase::active()
                ->where('purchase_date', 'like', $ym . '-%')
                ->where('status', 'completed')
                ->update(['status' => 'recorded']);

            // 在庫残高 翌月の前月繰越をリセット
            $nextYm = Carbon::createFromFormat('Y-m', $ym)->addMonth()->format('Y-m');
            InventoryBalance::where('stock_ym', $nextYm)->update(['prev_stock' => 0]);

            // システム設定更新
            SystemSetting::instance()->update(['closing_ym' => $ym]);
        });
    }
}
