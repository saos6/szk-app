<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>入金確認書 {{ $payment->payment_number }}</title>
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
    .doc-title {
        font-size: 22px;
        font-weight: bold;
        letter-spacing: 6px;
        text-align: center;
        border-bottom: 2px solid #1a1a1a;
        padding-bottom: 6px;
        margin-bottom: 16px;
    }
    .header-body { width: 100%; }
    .header-body td { vertical-align: top; }
    .to-block { width: 55%; }
    .customer-name {
        font-size: 16px;
        font-weight: bold;
        border-bottom: 1px solid #333;
        padding-bottom: 2px;
        margin-bottom: 4px;
    }
    .honorific { font-size: 13px; }
    .subject-row { margin-top: 10px; font-size: 13px; }
    .subject-label { color: #555; margin-right: 6px; }
    .total-box {
        border: 2px solid #1a1a1a;
        display: inline-block;
        padding: 6px 20px;
        margin: 14px 0 14px 0;
        text-align: center;
    }
    .total-box .label { font-size: 11px; color: #555; margin-bottom: 2px; }
    .total-box .amount { font-size: 20px; font-weight: bold; }
    .from-block { width: 44%; text-align: right; }
    .company-info { font-size: 10px; color: #333; line-height: 1.6; margin-bottom: 8px; }
    .company-name-main { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
    .meta-table { width: 100%; border-collapse: collapse; }
    .meta-table td { padding: 2px 4px; font-size: 11px; }
    .meta-label { color: #555; white-space: nowrap; }
    .meta-value { font-weight: bold; }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 10.5px;
    }
    .items-table th {
        background: #2c2c2c;
        color: #fff;
        padding: 5px 6px;
        text-align: center;
        font-weight: bold;
        border: 1px solid #2c2c2c;
    }
    .items-table td {
        padding: 5px 6px;
        border: 1px solid #ccc;
        vertical-align: middle;
    }
    .items-table tbody tr:nth-child(even) { background: #f7f7f7; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .summary-table {
        width: 220px;
        margin-left: auto;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 11px;
    }
    .summary-table td { padding: 4px 8px; border: 1px solid #ccc; }
    .summary-table .s-label { color: #444; width: 110px; }
    .summary-table .s-value { text-align: right; font-weight: bold; }
    .summary-table .total-row td {
        background: #2c2c2c;
        color: #fff;
        font-size: 13px;
        font-weight: bold;
    }
    .remarks-section { margin-top: 14px; }
    .remarks-label { font-weight: bold; color: #444; margin-bottom: 4px; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
    .remarks-body { font-size: 10.5px; white-space: pre-wrap; color: #333; margin-top: 4px; }
</style>
</head>
<body>

<div class="doc-title">入　金　確　認　書</div>

<table class="header-body" cellspacing="0" cellpadding="0">
<tr>
    <td class="to-block">
        <div class="customer-name">{{ $payment->customer?->name ?? '—' }} <span class="honorific">御中</span></div>
        <div class="subject-row">
            <span class="subject-label">件名：</span><strong>{{ $payment->subject }}</strong>
        </div>
        <div class="total-box" style="margin-top:14px;">
            <div class="label">入金合計</div>
            <div class="amount">{{ '¥' . number_format((float)$payment->total_amount) }}</div>
        </div>
    </td>
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
        <table class="meta-table" cellspacing="0" cellpadding="0">
            <tr>
                <td class="meta-label">入金番号</td>
                <td class="meta-value">{{ $payment->payment_number }}</td>
            </tr>
            <tr>
                <td class="meta-label">入金日</td>
                <td class="meta-value">{{ $payment->payment_date?->format('Y年m月d日') }}</td>
            </tr>
            @if($payment->employee)
            <tr>
                <td class="meta-label">担当者</td>
                <td class="meta-value">{{ $payment->employee->name }}</td>
            </tr>
            @endif
        </table>
    </td>
</tr>
</table>

<table class="items-table" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th style="width:4%">No.</th>
            <th style="width:15%">入金区分</th>
            <th style="width:18%">入金額</th>
            <th style="width:35%">銀行情報</th>
            <th style="width:28%">備考</th>
        </tr>
    </thead>
    <tbody>
        @php $types = \App\Models\Payment::PAYMENT_TYPES; @endphp
        @foreach($payment->items as $item)
        <tr>
            <td class="text-center">{{ $item->line_no }}</td>
            <td class="text-center">{{ $types[$item->payment_type] ?? $item->payment_type }}</td>
            <td class="text-right">¥{{ number_format((float)$item->amount) }}</td>
            <td>{{ $item->bank_info ?? '' }}</td>
            <td>{{ $item->remarks ?? '' }}</td>
        </tr>
        @endforeach
        @for($i = count($payment->items); $i < 5; $i++)
        <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
        @endfor
    </tbody>
</table>

<table class="summary-table" cellspacing="0" cellpadding="0">
    <tr class="total-row">
        <td class="s-label">合計入金額</td>
        <td class="s-value">¥{{ number_format((float)$payment->total_amount) }}</td>
    </tr>
</table>

@if($payment->remarks)
<div class="remarks-section">
    <div class="remarks-label">備考</div>
    <div class="remarks-body">{{ $payment->remarks }}</div>
</div>
@endif

</body>
</html>
