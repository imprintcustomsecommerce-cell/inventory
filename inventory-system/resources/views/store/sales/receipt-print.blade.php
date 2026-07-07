<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #ccc; }
        body { color: #000; }
        .paper {
            width: 80mm;
            margin: 0 auto;
            padding: 4mm 3mm;
            background: #fff;
            font-size: 12px;
        }
        .center { text-align: center; }
        .shop { font-size: 16px; font-weight: bold; }
        .muted { color: #444; font-size: 10px; }
        .hr { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .total td { font-size: 14px; font-weight: bold; padding-top: 6px; }
        .foot { margin-top: 10px; text-align: center; font-size: 10px; color: #444; }
        .toolbar {
            width: 80mm;
            margin: 0 auto 8px;
            display: flex;
            gap: 8px;
        }
        .toolbar button, .toolbar a {
            flex: 1;
            text-align: center;
            padding: 10px;
            font-size: 14px;
            border-radius: 6px;
            border: 1px solid #999;
            background: #fff;
            color: #111;
            text-decoration: none;
        }
        .toolbar button { background: #111; color: #fff; border-color: #111; }

        @media print {
            html, body { background: #fff; }
            .toolbar { display: none; }
            .paper { width: auto; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <a href="{{ route('sales.index') }}">Done</a>
    </div>

    <div class="paper">
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
            <tr>
                <td>{{ $sale->item_label }}</td>
                <td class="right">&nbsp;</td>
            </tr>
            <tr>
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
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
