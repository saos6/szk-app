<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>請求書 {{ $billingDate }}</title>
<style>
    @font-face {
        font-family: 'NotoSansJP';
        src: url('{{ str_replace('\\', '/', storage_path('fonts/NotoSansJP-Regular.ttf')) }}') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: 'NotoSansJP';
        src: url('{{ str_replace('\\', '/', storage_path('fonts/NotoSansJP-Regular.ttf')) }}') format('truetype');
        font-weight: bold;
        font-style: normal;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'NotoSansJP', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        background: #fff;
        padding: 20mm 18mm 20mm 18mm;
    }
    .page-break { page-break-after: always; }
    /* ─── タイトル ─── */
    .doc-title {
        font-size: 22px;
        font-weight: bold;
        letter-spacing: 6px;
        text-align: center;
        border-bottom: 2px solid #1a1a1a;
        padding-bottom: 6px;
        margin-bottom: 16px;
    }
    /* ─── ヘッダー ─── */
    .header-body { width: 100%; border-collapse: collapse; }
    .header-body td { vertical-align: top; }
    .to-block { width: 55%; }
    .customer-name { font-size: 16px; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 2px; margin-bottom: 4px; }
    .honorific { font-size: 13px; }
    .from-block { width: 44%; text-align: right; }
    .company-info { font-size: 10px; color: #333; line-height: 1.6; margin-bottom: 8px; }
    .company-name-main { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
    .meta-table { width: 100%; border-collapse: collapse; }
    .meta-table td { padding: 2px 4px; font-size: 11px; }
    .meta-label { color: #555; white-space: nowrap; }
    .meta-value { font-weight: bold; }
    /* ─── 集計サマリ ─── */
    .summary-wrap { margin-top: 14px; }
    .summary-table { width: 100%; border-collapse: collapse; }
    .summary-table td { padding: 5px 8px; border: 1px solid #ccc; font-size: 11px; }
    .summary-label { background: #f5f5f5; color: #444; white-space: nowrap; width: 38%; }
    .summary-value { text-align: right; font-weight: bold; }
    .summary-total td { background: #1a1a1a; color: #fff; font-weight: bold; font-size: 13px; }
    .summary-balance td { background: #e8f4e8; font-weight: bold; font-size: 13px; }
    /* ─── 明細テーブル ─── */
    .section-title { font-size: 12px; font-weight: bold; margin: 14px 0 6px; border-left: 3px solid #1a1a1a; padding-left: 6px; }
    .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .detail-table th { background: #f5f5f5; font-weight: bold; padding: 5px 6px; border: 1px solid #ccc; font-size: 10px; text-align: left; }
    .detail-table td { padding: 4px 6px; border: 1px solid #e0e0e0; font-size: 10px; }
    .detail-header-row { background: #eef2f7; }
    .detail-header-row td { font-weight: bold; font-size: 10px; border-top: 1px solid #aac; border-bottom: 1px solid #aac; }
    .item-row td { background: #fafbfd; font-size: 9px; padding-left: 14px; }
    th.item-header-row { background: #e8ecf5; font-size: 9px; padding: 3px 6px 3px 14px; border: 1px solid #ccc; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    /* ─── 振込先 ─── */
    .bank-section { margin-top: 14px; border: 1px solid #ccc; padding: 8px 10px; font-size: 10px; background: #fafafa; }
    .bank-title { font-weight: bold; margin-bottom: 4px; font-size: 11px; }
    .bank-row { display: inline-block; margin-right: 16px; }
</style>
</head>
<body>

@foreach($rows as $index => $row)
@php
    $customer = $row['customer'];
    $sales    = collect($row['sales'] ?? []);
    $payments = collect($row['payments'] ?? []);
    $billingNum = $row['billing_number'] ?? ('（集計中）');
    $startDate  = $row['closing_start_date']
        ? \Carbon\Carbon::parse($row['closing_start_date'])->format('Y年n月j日')
        : '—';
    $endDate    = \Carbon\Carbon::parse($billingDate)->format('Y年n月j日');
    $fmtAmt     = fn($v) => '¥' . number_format((float)$v, 0);
@endphp

<div @if(!$loop->last) class="page-break" @endif>

    <div class="doc-title">請　求　書</div>

    <table class="header-body">
        <tr>
            <!-- 宛先 -->
            <td class="to-block">
                <div class="customer-name">{{ $customer->name }}</div>
                <div class="honorific">御中</div>
                @if($customer->address)
                    <div style="margin-top:4px;font-size:10px;color:#555;">〒{{ $customer->postal_code }}　{{ $customer->address }}</div>
                @endif
                <div style="margin-top:10px;font-size:12px;">
                    <span style="color:#555;">請求期間：</span>
                    <strong>{{ $startDate }} 〜 {{ $endDate }}</strong>
                </div>
            </td>
            <!-- 発行情報 -->
            <td class="from-block">
                <!-- 自社情報 -->
                <div class="company-info">
                    @if($setting->company_name)
                    <div class="company-name-main">{{ $setting->company_name }}</div>
                    @endif
                    @if($setting->postal_code)
                    <div>〒{{ $setting->postal_code }}</div>
                    @endif
                    @if($setting->prefecture_city || $setting->address)
                    <div>{{ $setting->prefecture_city }}{{ $setting->address }}</div>
                    @endif
                    @if($setting->building)
                    <div>{{ $setting->building }}</div>
                    @endif
                    @if($setting->tel)
                    <div>TEL: {{ $setting->tel }}</div>
                    @endif
                    @if($setting->fax)
                    <div>FAX: {{ $setting->fax }}</div>
                    @endif
                    @if($setting->invoice_no)
                    <div>登録番号: {{ $setting->invoice_no }}</div>
                    @endif
                </div>
                <table class="meta-table">
                    <tr><td class="meta-label">請求番号</td><td class="meta-value">{{ $billingNum }}</td></tr>
                    <tr><td class="meta-label">請求日</td><td class="meta-value">{{ $endDate }}</td></tr>
                    @if($customer->payment_day)
                    <tr>
                        <td class="meta-label">お支払期限</td>
                        <td class="meta-value">
                            @php
                                $pd = (int)$customer->payment_day;
                                $nextMonth = \Carbon\Carbon::parse($billingDate)->addMonth();
                                $due = $pd === 31
                                    ? $nextMonth->endOfMonth()->format('Y年n月j日')
                                    : $nextMonth->day(min($pd, $nextMonth->daysInMonth))->format('Y年n月j日');
                            @endphp
                            {{ $due }}
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- サマリ -->
    <div class="summary-wrap">
        <table class="summary-table">
            <tr>
                <td class="summary-label">前回繰越金額</td>
                <td class="summary-value">{{ $fmtAmt($row['prev_amount']) }}</td>
            </tr>
            <tr>
                <td class="summary-label">今回売上金額（税抜）</td>
                <td class="summary-value">{{ $fmtAmt($row['sales_amount']) }}</td>
            </tr>
            <tr>
                <td class="summary-label">消費税</td>
                <td class="summary-value">{{ $fmtAmt($row['tax_amount']) }}</td>
            </tr>
            <tr class="summary-total">
                <td class="summary-label" style="color:#fff;">ご請求合計</td>
                <td class="summary-value">{{ $fmtAmt($row['total_amount']) }}</td>
            </tr>
            <tr>
                <td class="summary-label">当期入金額</td>
                <td class="summary-value" style="color:#1a6bbf;">△ {{ $fmtAmt($row['payment_amount']) }}</td>
            </tr>
            <tr class="summary-balance">
                <td class="summary-label">残高（次回繰越）</td>
                <td class="summary-value">{{ $fmtAmt($row['balance_amount']) }}</td>
            </tr>
        </table>
    </div>

    <!-- 売上明細 -->
    @if($sales->isNotEmpty())
    <div class="section-title">売上明細（{{ $sales->count() }}件）</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:16%">売上番号</th>
                <th style="width:8%">売上区分</th>
                <th style="width:13%">売上日</th>
                <th>件名</th>
                <th style="width:15%;text-align:right">税抜金額</th>
                <th style="width:11%;text-align:right">消費税</th>
                <th style="width:15%;text-align:right">税込金額</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            @php $saleItems = $sale->relationLoaded('items') ? $sale->items : collect(); @endphp
            <tr class="detail-header-row">
                <td class="text-center" style="font-size:9px;">{{ $sale->sale_number }}</td>
                <td class="text-center">{{ $sale->sale_type ? (\App\Models\Sale::SALE_TYPES[$sale->sale_type] ?? $sale->sale_type) : '—' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y/m/d') }}</td>
                <td>{{ $sale->subject }}</td>
                <td class="text-right">{{ $fmtAmt($sale->subtotal) }}</td>
                <td class="text-right">{{ $fmtAmt($sale->tax_amount) }}</td>
                <td class="text-right">{{ $fmtAmt($sale->total_amount) }}</td>
            </tr>
            @if($saleItems->isNotEmpty())
            <tr>
                <th class="item-header-row" style="width:18%;padding-left:14px">機種コード</th>
                <th class="item-header-row" style="width:22%">フレームNo</th>
                <th class="item-header-row" style="width:6%">色</th>
                <th class="item-header-row">機種名</th>
                <th class="item-header-row" style="width:8%;text-align:right">数量</th>
                <th class="item-header-row" style="width:14%;text-align:right">売上単価</th>
                <th class="item-header-row" style="width:14%;text-align:right">売上金額</th>
            </tr>
            @foreach($saleItems as $si)
            <tr class="item-row">
                <td>{{ $si->model_code }}</td>
                <td>{{ $si->frame_number }}</td>
                <td class="text-center">{{ $si->color_code }}</td>
                <td>{{ $si->model_name }}</td>
                <td class="text-right">{{ rtrim(rtrim(number_format((float)$si->quantity, 2), '0'), '.') }}{{ $si->unit ? ' '.$si->unit : '' }}</td>
                <td class="text-right">{{ $fmtAmt($si->selling_price) }}</td>
                <td class="text-right">{{ $fmtAmt($si->sale_amount) }}</td>
            </tr>
            @endforeach
            @endif
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- 入金明細 -->
    @if($payments->isNotEmpty())
    <div class="section-title">入金明細（{{ $payments->count() }}件）</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:22%">入金番号</th>
                <th style="width:18%">入金日</th>
                <th>件名</th>
                <th style="width:20%;text-align:right">入金額</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            @php $paymentItems = $payment->relationLoaded('items') ? $payment->items : collect(); @endphp
            <tr class="detail-header-row">
                <td class="text-center" style="font-size:9px;">{{ $payment->payment_number }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') }}</td>
                <td>{{ $payment->subject }}</td>
                <td class="text-right">{{ $fmtAmt($payment->total_amount) }}</td>
            </tr>
            @if($paymentItems->isNotEmpty())
            <tr>
                <th class="item-header-row" colspan="2" style="padding-left:14px">入金区分</th>
                <th class="item-header-row">銀行情報</th>
                <th class="item-header-row" style="text-align:right">金額</th>
            </tr>
            @foreach($paymentItems as $pi)
            <tr class="item-row">
                <td colspan="2">{{ $pi->payment_type }}</td>
                <td>{{ $pi->bank_info }}</td>
                <td class="text-right">{{ $fmtAmt($pi->amount) }}</td>
            </tr>
            @endforeach
            @endif
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- 振込先情報 -->
    @if($setting->bank_info || $setting->account_number || $setting->account_holder)
    <div class="bank-section">
        <div class="bank-title">振込先</div>
        @if($setting->bank_info)
        <span class="bank-row">{{ $setting->bank_info }}</span>
        @endif
        @if($setting->account_number)
        <span class="bank-row">口座番号: {{ $setting->account_number }}</span>
        @endif
        @if($setting->account_holder)
        <span class="bank-row">口座名義: {{ $setting->account_holder }}</span>
        @endif
    </div>
    @endif

</div>
@endforeach

</body>
</html>
