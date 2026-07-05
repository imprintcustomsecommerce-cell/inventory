<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; color: #000; font-size: 11px; }
        .center { text-align: center; }
        .shop { font-size: 15px; font-weight: bold; }
        .muted { color: #444; font-size: 10px; }
        .hr { border-top: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .row td { font-size: 11px; }
        .total td { font-size: 13px; font-weight: bold; padding-top: 6px; }
        .foot { margin-top: 12px; text-align: center; font-size: 10px; color: #444; }
    </style>
</head>
<body>
    <div class="center">
        <div class="shop">IMPRINT CUSTOMS</div>
        <div class="muted">Custom Apparel · Est. 2013</div>
    </div>

    <div class="hr"></div>

    <table>
        <tr><td>Receipt</td><td class="right">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><td>Date</td><td class="right">{{ $sale->created_at->format('M d, Y h:i A') }}</td></tr>
        <tr><td>Location</td><td class="right">{{ $sale->warehouse?->name ?? '—' }}</td></tr>
        <tr><td>Cashier</td><td class="right">{{ $sale->user?->name ?? '—' }}</td></tr>
    </table>

    <div class="hr"></div>

    <table>
        <tr class="row">
            <td>{{ $sale->item_label }}</td>
            <td class="right">&nbsp;</td>
        </tr>
        <tr class="row">
            <td class="muted">{{ rtrim(rtrim(number_format($sale->quantity, 2), '0'), '.') }} × ₱{{ number_format($sale->unit_price, 2) }}</td>
            <td class="right">₱{{ number_format($sale->total, 2) }}</td>
        </tr>
    </table>

    <div class="hr"></div>

    <table>
        <tr class="total"><td>TOTAL</td><td class="right">₱{{ number_format($sale->total, 2) }}</td></tr>
    </table>

    @if($sale->remarks)
        <div class="hr"></div>
        <div class="muted">{{ $sale->remarks }}</div>
    @endif

    <div class="foot">
        Thank you for your purchase!<br>
        This serves as your official receipt.
    </div>
</body>
</html>
