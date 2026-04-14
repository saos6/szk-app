<?php

namespace App\Services;

use App\Models\InventoryBalance;

/**
 * 在庫残高マスタの入出庫数を売上/仕入明細と連動して更新するサービス
 *
 * キー: stock_ym (YYYY-MM) × warehouse_code × model_code × frame_number
 * 売上 → out_stock を増減
 * 仕入 → in_stock  を増減
 */
class InventoryService
{
    /**
     * 明細を在庫残高に反映する（登録・更新後の新明細用）
     *
     * @param  string  $ym    YYYY-MM
     * @param  array   $items sale_items / purchase_items の配列
     * @param  string  $type  'in' (仕入) | 'out' (売上)
     */
    public static function applyItems(string $ym, array $items, string $type): void
    {
        foreach ($items as $item) {
            $fields = self::extractFields($item);
            if (! self::isTrackable($fields)) {
                continue;
            }

            self::adjust(
                $ym,
                $fields['warehouse_code'],
                $fields['model_code'],
                $fields['frame_number'],
                $type === 'in'  ? $fields['quantity'] : 0,
                $type === 'out' ? $fields['quantity'] : 0,
            );
        }
    }

    /**
     * 在庫残高への反映を取り消す（更新前の旧明細・削除時用）
     *
     * @param  string  $ym    YYYY-MM
     * @param  array   $items sale_items / purchase_items の配列
     * @param  string  $type  'in' (仕入) | 'out' (売上)
     */
    public static function reverseItems(string $ym, array $items, string $type): void
    {
        foreach ($items as $item) {
            $fields = self::extractFields($item);
            if (! self::isTrackable($fields)) {
                continue;
            }

            self::adjust(
                $ym,
                $fields['warehouse_code'],
                $fields['model_code'],
                $fields['frame_number'],
                $type === 'in'  ? -$fields['quantity'] : 0,
                $type === 'out' ? -$fields['quantity'] : 0,
            );
        }
    }

    // ─── Private helpers ──────────────────────────────────────────────

    /** @return array{model_code:?string, frame_number:?string, warehouse_code:?string, quantity:int} */
    private static function extractFields(array $item): array
    {
        return [
            'model_code'     => ($item['model_code'] ?? '') ?: null,
            'frame_number'   => ($item['frame_number'] ?? '') ?: null,
            'warehouse_code' => ($item['warehouse_code'] ?? '') ?: null,
            'quantity'       => (int) round((float) ($item['quantity'] ?? 0)),
        ];
    }

    private static function isTrackable(array $fields): bool
    {
        return $fields['model_code'] !== null
            && $fields['frame_number'] !== null
            && $fields['warehouse_code'] !== null
            && $fields['quantity'] > 0;
    }

    private static function adjust(
        string $ym,
        string $wareCode,
        string $modelCode,
        string $frameNumber,
        int $inDelta,
        int $outDelta,
    ): void {
        $balance = InventoryBalance::firstOrCreate(
            [
                'stock_ym'       => $ym,
                'warehouse_code' => $wareCode,
                'model_code'     => $modelCode,
                'frame_number'   => $frameNumber,
            ],
            ['prev_stock' => 0, 'in_stock' => 0, 'out_stock' => 0],
        );

        $balance->in_stock  += $inDelta;
        $balance->out_stock += $outDelta;
        $balance->save();
    }
}
