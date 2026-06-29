<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #111; font-size: 12px; margin: 0; }
        .header { border-bottom: 3px solid #facc15; padding-bottom: 14px; margin-bottom: 20px; }
        .header table { width: 100%; }
        .logo { width: 54px; height: 54px; background: #facc15; color: #111; border-radius: 8px; text-align: center; font-weight: bold; font-size: 22px; line-height: 54px; }
        .company { font-size: 18px; font-weight: bold; }
        .muted { color: #777; }
        .title { font-size: 22px; font-weight: bold; margin: 0; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; background: #f3f4f6; font-size: 11px; font-weight: bold; }
        .grid { width: 100%; margin: 18px 0; }
        .grid td { vertical-align: top; padding: 4px 0; }
        .label { color: #777; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
        .value { font-size: 13px; font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { background: #111; color: #fff; text-align: left; padding: 8px 10px; font-size: 11px; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        table.items tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .totals { width: 45%; margin-left: 55%; margin-top: 10px; }
        .totals td { padding: 5px 10px; }
        .totals .grand { border-top: 2px solid #111; font-weight: bold; font-size: 14px; }
        .totals .due { font-weight: bold; font-size: 14px; color: #b91c1c; }
        .section-title { font-size: 13px; font-weight: bold; margin: 22px 0 6px; }
        .footer { margin-top: 40px; color: #999; font-size: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width:70px;">
                    <div class="logo">IC</div>
                </td>
                <td>
                    <div class="company">Imprint Customs</div>
                    <div class="muted">Custom Apparel · Established 2013</div>
                </td>
                <td class="text-right">
                    <p class="title">INVOICE</p>
                    <div class="muted">{{ $invoice->invoice_number }}</div>
                    <div class="muted">{{ $invoice->issue_date?->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td style="width:50%;">
                <div class="label">Bill to</div>
                <div class="value">{{ $invoice->customer?->name ?? '—' }}</div>
                @if($invoice->customer?->company)<div class="muted">{{ $invoice->customer->company }}</div>@endif
                @if($invoice->customer?->email)<div class="muted">{{ $invoice->customer->email }}</div>@endif
                @if($invoice->customer?->phone)<div class="muted">{{ $invoice->customer->phone }}</div>@endif
            </td>
            <td style="width:25%;">
                <div class="label">Subject</div>
                <div class="value">{{ $invoice->title }}</div>
            </td>
            <td style="width:25%;">
                <div class="label">Due date</div>
                <div class="value">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</div>
                <div class="label" style="margin-top:8px;">Status</div>
                <div><span class="badge">{{ $invoice->status }}</span></div>
            </td>
        </tr>
    </table>

    <div class="section-title">Items</div>
    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">₱{{ number_format($item->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No items listed.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="muted">Subtotal</td>
            <td class="text-right">₱{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="muted">Discount</td>
            <td class="text-right">− ₱{{ number_format($invoice->discount, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="text-right">₱{{ number_format($invoice->total, 2) }}</td>
        </tr>
        <tr>
            <td class="muted">Paid</td>
            <td class="text-right">₱{{ number_format($invoice->amount_paid, 2) }}</td>
        </tr>
        <tr class="due">
            <td>Balance due</td>
            <td class="text-right">₱{{ number_format($invoice->balance(), 2) }}</td>
        </tr>
    </table>

    @if($invoice->payments->count() > 0)
        <div class="section-title">Payments received</div>
        <table class="items">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                        <td>{{ $payment->method }}</td>
                        <td>{{ $payment->reference ?? '—' }}</td>
                        <td class="text-right">₱{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($invoice->notes)
        <div class="section-title">Notes</div>
        <div>{{ $invoice->notes }}</div>
    @endif

    @if($invoice->terms)
        <div class="section-title">Terms &amp; conditions</div>
        <div>{{ $invoice->terms }}</div>
    @endif

    <div class="footer">Generated by Imprint Inventory System · {{ now()->format('M d, Y h:i A') }}</div>
</body>
</html>
