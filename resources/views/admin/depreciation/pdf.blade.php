<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Depreciation management</title>
<style>
    @page { margin: 28px 32px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111; }
    .head { background: #000; color: #fff; padding: 12px 14px; }
    .head h1 { margin: 0; font-size: 16px; }
    .head p { margin: 3px 0 0; color: #00AEEF; font-size: 9px; }
    .rule { height: 3px; background: #00AEEF; margin-bottom: 12px; }
    table.kpi { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 12px; }
    table.kpi td { border: 1px solid #cfd8dc; padding: 8px; width: 25%; }
    .kpi .l { color: #555; font-size: 9px; }
    .kpi .v { font-size: 15px; font-weight: bold; margin-top: 3px; }
    h2 { font-size: 12px; margin: 14px 0 6px; border-bottom: 2px solid #00AEEF; padding-bottom: 3px; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #E6F7FD; text-align: left; border: 1px solid #b9d9e6; padding: 5px 6px; }
    table.data td { border: 1px solid #cfd8dc; padding: 5px 6px; }
    .r { text-align: right; }
    .foot { margin-top: 10px; color: #555; font-size: 8px; }
</style>
</head>
<body>
<div class="head">
    <h1>DOST-PES Asset Lifecycle Management System</h1>
    <p>Depreciation management &middot; Generated {{ now()->format('d M Y H:i') }} &middot; Straight-line method</p>
</div>
<div class="rule"></div>

<table class="kpi">
    <tr>
        <td><div class="l">Total acquisition value</div><div class="v">&#8369;{{ number_format($totalAcquisitionValue, 2) }}</div></td>
        <td><div class="l">Current book value</div><div class="v">&#8369;{{ number_format($currentBookValue, 2) }}</div></td>
        <td><div class="l">YTD depreciation (FY {{ now()->year }})</div><div class="v">&#8369;{{ number_format($ytdDepreciation, 2) }}</div></td>
        <td><div class="l">Fully depreciated</div><div class="v">{{ $fullyDepreciatedCount }}</div></td>
    </tr>
</table>

<h2>Depreciation by department</h2>
<table class="data">
    <thead><tr><th>Department</th><th class="r">Accumulated depreciation</th></tr></thead>
    <tbody>
    @foreach ($byDepartment as $department => $amount)
        <tr><td>{{ $department }}</td><td class="r">&#8369;{{ number_format($amount, 2) }}</td></tr>
    @endforeach
    </tbody>
</table>

<h2>Asset depreciation schedule</h2>
<table class="data">
    <thead>
        <tr>
            <th>Asset ID</th><th>Asset name</th><th class="r">Acq. cost</th><th class="r">Book value</th>
            <th class="r">Depreciation</th><th>Method</th><th>Useful life</th><th>Remaining</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($assets as $asset)
        <tr>
            <td>{{ $asset->asset_tag }}</td>
            <td>{{ $asset->name }}</td>
            <td class="r">&#8369;{{ number_format($asset->acquisition_cost, 2) }}</td>
            <td class="r">&#8369;{{ number_format($asset->currentBookValue(), 2) }}</td>
            <td class="r">&#8369;{{ number_format($asset->accumulatedDepreciation(), 2) }}</td>
            <td>Straight-line</td>
            <td>{{ $asset->useful_life_years }} years</td>
            <td>{{ $asset->remainingLifeLabel() }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="foot">Each year an asset loses (acquisition cost &minus; salvage value) &divide; useful life in years; book value never falls below salvage value.</div>
</body>
</html>
