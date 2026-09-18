<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #334155; }
        h1 { font-size: 18px; color: #0f2d5e; margin-bottom: 2px; }
        .sub { color: #94a3b8; font-size: 10px; margin-bottom: 18px; }
        h2 { font-size: 13px; color: #0f2d5e; border-bottom: 1px solid #e5e9f0; padding-bottom: 4px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { text-align: left; font-size: 9px; text-transform: uppercase; color: #94a3b8; padding: 5px 0; border-bottom: 1px solid #e5e9f0; }
        td { padding: 5px 0; border-bottom: 0.5px solid #f1f5f9; font-size: 10.5px; }
        .insight-box { border-left: 3px solid #4a9eff; padding-left: 10px; margin-bottom: 10px; }
        .insight-label { font-size: 9px; color: #94a3b8; }
        .insight-value { font-size: 12px; font-weight: bold; color: #0f2d5e; }
    </style>
</head>
<body>
    <h1>Reports &amp; Analytics</h1>
    <div class="sub">
        Generated {{ now()->format('M j, Y g:i A') }}
        @if ($dateFrom) — {{ $dateFrom->format('M j, Y') }} to {{ $dateTo->format('M j, Y') }} @endif
    </div>

    <h2>Asset Utilization by Department</h2>
    <table>
        <tr><th>Department</th><th>Utilization Rate</th></tr>
        @foreach ($utilization as $row)
            <tr><td>{{ $row['name'] }}</td><td>{{ $row['rate'] }}%</td></tr>
        @endforeach
    </table>

    <h2>Depreciation Summary by Year</h2>
    <table>
        <tr><th>Year</th><th>Depreciation (₱)</th></tr>
        @foreach ($depreciationByYear as $row)
            <tr><td>{{ $row['year'] }}</td><td>₱{{ number_format($row['amount'], 2) }}</td></tr>
        @endforeach
    </table>

    <h2>Maintenance Costs by Quarter</h2>
    <table>
        <tr><th>Quarter</th><th>Cost (₱)</th></tr>
        @foreach ($costByQuarter as $row)
            <tr><td>{{ $row['quarter'] }}</td><td>₱{{ number_format($row['amount'], 2) }}</td></tr>
        @endforeach
    </table>

    <h2>Asset Status Distribution</h2>
    <table>
        <tr><th>Status</th><th>Count</th><th>%</th></tr>
        @foreach ($statusCounts as $status => $count)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                <td>{{ $count }}</td>
                <td>{{ $totalAssetsCount > 0 ? round(($count / $totalAssetsCount) * 100) : 0 }}%</td>
            </tr>
        @endforeach
    </table>

    <h2>Data Insights</h2>
    <div class="insight-box">
        <div class="insight-label">Highest depreciation category</div>
        <div class="insight-value">{{ $highestDepCategoryName ?? 'No data' }} @if($highestDepCategoryValue) — ₱{{ number_format($highestDepCategoryValue, 0) }} @endif</div>
    </div>
    <div class="insight-box">
        <div class="insight-label">Most maintained asset</div>
        <div class="insight-value">{{ $mostMaintained->name ?? 'No data' }} @if($mostMaintained && $mostMaintained->work_orders_count > 0)({{ $mostMaintained->work_orders_count }} repairs)@endif</div>
    </div>
    <div class="insight-box">
        <div class="insight-label">Average useful life</div>
        <div class="insight-value">{{ $avgLifespan ? number_format($avgLifespan, 1) . ' years' : 'No data' }}</div>
    </div>
</body>
</html>
