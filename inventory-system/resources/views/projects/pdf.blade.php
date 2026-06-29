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
        .totals { width: 40%; margin-left: 60%; margin-top: 10px; }
        .totals td { padding: 5px 10px; }
        .totals .grand { border-top: 2px solid #111; font-weight: bold; font-size: 14px; }
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
                    <p class="title">JOB ORDER</p>
                    <div class="muted">#{{ str_pad($project->id, 5, '0', STR_PAD_LEFT) }}</div>
                    <div class="muted">{{ now()->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td style="width:50%;">
                <div class="label">Project</div>
                <div class="value">{{ $project->project_name }}</div>
            </td>
            <td style="width:25%;">
                <div class="label">Customer</div>
                <div class="value">{{ $project->customer_name ?? '—' }}</div>
            </td>
            <td style="width:25%;">
                <div class="label">Status</div>
                <div><span class="badge">{{ $project->status }}</span></div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Product</div>
                <div class="value">{{ $project->product_type ?? '—' }}</div>
            </td>
            <td>
                <div class="label">Quantity</div>
                <div class="value">{{ $project->quantity }}</div>
            </td>
            <td>
                <div class="label">Due date</div>
                <div class="value">{{ $project->due_date ? $project->due_date->format('M d, Y') : '—' }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Materials</div>
    <table class="items">
        <thead>
            <tr>
                <th>Material</th>
                <th class="text-right">Qty needed</th>
                <th class="text-right">Unit cost</th>
                <th class="text-right">Line cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse($project->materials as $m)
                @php $line = (float) $m->quantity_needed * (float) ($m->inventoryItem->unit_cost ?? 0); @endphp
                <tr>
                    <td>{{ $m->inventoryItem->name }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format($m->quantity_needed, 2), '0'), '.') }} {{ $m->inventoryItem->unit }}</td>
                    <td class="text-right">₱{{ number_format($m->inventoryItem->unit_cost ?? 0, 2) }}</td>
                    <td class="text-right">₱{{ number_format($line, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No materials listed.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($project->labor->count() > 0)
        <div class="section-title">Labor</div>
        <table class="items">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Worker</th>
                    <th>Task</th>
                    <th class="text-right">Hours</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->labor as $l)
                    <tr>
                        <td>{{ $l->logged_at?->format('M d, Y') }}</td>
                        <td>{{ $l->workerLabel() }}</td>
                        <td>{{ $l->task }}</td>
                        <td class="text-right">{{ rtrim(rtrim(number_format($l->hours, 2), '0'), '.') }}</td>
                        <td class="text-right">₱{{ number_format($l->hourly_rate, 2) }}</td>
                        <td class="text-right">₱{{ number_format($l->cost(), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @php $cost = $project->materialsCost(); $labor = $project->laborCost(); $margin = $project->margin(); @endphp
    <table class="totals">
        <tr>
            <td class="muted">Material cost</td>
            <td class="text-right">₱{{ number_format($cost, 2) }}</td>
        </tr>
        <tr>
            <td class="muted">Labor cost</td>
            <td class="text-right">₱{{ number_format($labor, 2) }}</td>
        </tr>
        <tr>
            <td class="muted">Total cost</td>
            <td class="text-right">₱{{ number_format($cost + $labor, 2) }}</td>
        </tr>
        @if($project->quoted_price !== null)
            <tr>
                <td class="muted">Quoted price</td>
                <td class="text-right">₱{{ number_format($project->quoted_price, 2) }}</td>
            </tr>
            <tr class="grand">
                <td>Margin</td>
                <td class="text-right">₱{{ number_format($margin, 2) }}</td>
            </tr>
        @endif
    </table>

    @if($project->remarks)
        <div class="section-title">Remarks</div>
        <div>{{ $project->remarks }}</div>
    @endif

    <div class="footer">Generated by Imprint Inventory System · {{ now()->format('M d, Y h:i A') }}</div>
</body>
</html>
