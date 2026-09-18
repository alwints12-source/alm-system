<x-prototype-layout title="Depreciation">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Administrative Admin</div>
            <div class="pt">Depreciation management</div>
        </div>
        <div style="display:flex;gap:8px">
            <button class="btn sm" disabled title="Coming in a later sprint"><i class="ti ti-file-type-pdf" style="font-size:13px"></i> PDF</button>
            <a href="{{ route('admin.depreciation.export') }}" class="btn sm"><i class="ti ti-file-type-xls" style="font-size:13px"></i> Excel (CSV)</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px">
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px">Total acquisition value</div>
            <div style="font-size:26px;font-weight:700;color:#0f2d5e">₱{{ number_format($totalAcquisitionValue, 0) }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">All active assets</div>
        </div>
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px">Current book value</div>
            <div style="font-size:26px;font-weight:700;color:#0f2d5e">₱{{ number_format($currentBookValue, 0) }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">After depreciation</div>
        </div>
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px">YTD depreciation</div>
            <div style="font-size:26px;font-weight:700;color:#7a4a0a">₱{{ number_format($ytdDepreciation, 0) }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">FY {{ now()->year }}</div>
        </div>
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px">Fully depreciated</div>
            <div style="font-size:26px;font-weight:700;color:#0f2d5e">{{ $fullyDepreciatedCount }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">Book value = salvage floor</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
        <div class="card">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:16px">Depreciation by department</div>
            @forelse ($byDepartment as $department => $amount)
                <div style="margin-bottom:14px">
                    <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px">
                        <span style="color:#334155">{{ $department }}</span>
                        <span style="color:#64748b">₱{{ number_format($amount, 0) }}</span>
                    </div>
                    <div style="background:#f1f5f9;border-radius:4px;height:8px;overflow:hidden">
                        <div style="background:#4a9eff;height:100%;width:{{ $maxDepartmentValue > 0 ? ($amount / $maxDepartmentValue) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No depreciable assets yet.</div>
            @endforelse
        </div>

        <div class="card">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:4px">Depreciation schedule (₱)</div>
            <div style="font-size:11px;color:#94a3b8;margin-bottom:12px">Calculated from actual acquisition dates — not a fixed range</div>
            @if ($yearlySchedule->count() > 0)
                <canvas id="scheduleChart" style="max-height:240px"></canvas>
            @else
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No depreciable assets yet.</div>
            @endif
        </div>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>Asset ID</th>
                    <th>Asset name</th>
                    <th>Acq. cost</th>
                    <th>Book value</th>
                    <th>Deprec.</th>
                    <th>Method</th>
                    <th>Useful life</th>
                    <th>Remaining</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assets as $asset)
                    <tr>
                        <td>{{ $asset->asset_tag }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>₱{{ number_format($asset->acquisition_cost, 0) }}</td>
                        <td>₱{{ number_format($asset->currentBookValue(), 0) }}</td>
                        <td>₱{{ number_format($asset->accumulatedDepreciation(), 0) }}</td>
                        <td>Straight-line</td>
                        <td>{{ $asset->useful_life_years }} years</td>
                        <td>{{ $asset->remainingLifeLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;color:#64748b;padding:24px">No assets with useful life set yet — add depreciation details when registering an asset.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($yearlySchedule->count() > 0)
        <script>
            new Chart(document.getElementById('scheduleChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($yearlySchedule->pluck('year')) !!},
                    datasets: [{
                        label: 'Depreciation (₱)',
                        data: {!! json_encode($yearlySchedule->pluck('amount')) !!},
                        backgroundColor: '#4a9eff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        </script>
    @endif

</x-prototype-layout>
