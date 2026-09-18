<x-prototype-layout title="Reports & analytics">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Administrative Admin</div>
            <div class="pt">Reports & analytics</div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="btn sm"><i class="ti ti-file-type-pdf" style="font-size:13px"></i> PDF</a>
            <a href="{{ route('admin.reports.csv', request()->query()) }}" class="btn sm"><i class="ti ti-file-type-xls" style="font-size:13px"></i> Excel</a>
            <a href="{{ route('admin.reports.csv', request()->query()) }}" class="btn sm"><i class="ti ti-download" style="font-size:13px"></i> CSV</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.index') }}" style="display:flex;gap:10px;align-items:center;margin-bottom:20px;flex-wrap:wrap">
        <input type="date" name="date_from" value="{{ request('date_from') }}" style="padding:8px 10px;border:1px solid #e5e9f0;border-radius:6px;font-size:12.5px">
        <span style="color:#94a3b8;font-size:12.5px">to</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}" style="padding:8px 10px;border:1px solid #e5e9f0;border-radius:6px;font-size:12.5px">
        <select name="department_id" style="padding:8px 10px;border:1px solid #e5e9f0;border-radius:6px;font-size:12.5px">
            <option value="">All Departments</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" {{ $departmentId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
            @endforeach
        </select>
        <select name="category_id" style="padding:8px 10px;border:1px solid #e5e9f0;border-radius:6px;font-size:12.5px">
            <option value="">All Asset Types</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn pri sm">Apply filter</button>
        @if (request()->has('date_from') || request()->has('department_id') || request()->has('category_id'))
            <a href="{{ route('admin.reports.index') }}" class="btn sm">Clear</a>
        @endif
    </form>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
        <div class="card">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:4px">Asset utilization rate</div>
            <div style="font-size:11px;color:#94a3b8;margin-bottom:14px">Live snapshot — % of assets currently assigned, by department</div>
            @if ($utilization->count() > 0)
                <canvas id="utilizationChart" style="max-height:240px"></canvas>
            @else
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No location data available.</div>
            @endif
        </div>

        <div class="card">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:4px">Depreciation summary (₱)</div>
            <div style="font-size:11px;color:#94a3b8;margin-bottom:14px">Real span from actual acquisition dates — not a fixed range</div>
            @if ($depreciationByYear->count() > 0)
                <canvas id="depreciationChart" style="max-height:240px"></canvas>
            @else
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No depreciable assets match this filter.</div>
            @endif
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
        <div class="card">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:14px">Maintenance costs by quarter</div>
            @if ($costByQuarter->sum('amount') > 0)
                <canvas id="costChart" style="max-height:240px"></canvas>
            @else
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No completed work orders with recorded cost in this range.</div>
            @endif
        </div>
        <div class="card">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:16px">Asset status distribution</div>
            @if ($totalAssetsCount > 0)
                <canvas id="statusChart" style="max-height:220px"></canvas>
            @else
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No assets match this filter.</div>
            @endif
        </div>

    <div class="card">
        <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:14px">Data insights</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
            <div style="border-left:3px solid #4a9eff;padding-left:12px">
                <div style="font-size:11.5px;color:#94a3b8">Highest depreciation</div>
                <div style="font-size:15px;font-weight:700;color:#0f2d5e;margin-top:2px">
                    {{ $highestDepCategoryName ?? 'No data' }}
                    @if ($highestDepCategoryValue)
                        — ₱{{ number_format($highestDepCategoryValue, 0) }}
                    @endif
                </div>
            </div>
            <div style="border-left:3px solid #4a9eff;padding-left:12px">
                <div style="font-size:11.5px;color:#94a3b8">Most maintained</div>
                <div style="font-size:15px;font-weight:700;color:#0f2d5e;margin-top:2px">
                    {{ $mostMaintained->name ?? 'No data' }}
                    @if ($mostMaintained && $mostMaintained->work_orders_count > 0)
                        ({{ $mostMaintained->work_orders_count }} repairs)
                    @endif
                </div>
            </div>
            <div style="border-left:3px solid #4a9eff;padding-left:12px">
                <div style="font-size:11.5px;color:#94a3b8">Avg useful life</div>
                <div style="font-size:15px;font-weight:700;color:#0f2d5e;margin-top:2px">
                    {{ $avgLifespan ? number_format($avgLifespan, 1) . ' years' : 'No data' }}
                </div>
            </div>
        </div>
    </div>

    @if ($utilization->count() > 0)
        <script>
            new Chart(document.getElementById('utilizationChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($utilization->pluck('name')) !!},
                    datasets: [{ label: 'Utilization %', data: {!! json_encode($utilization->pluck('rate')) !!}, backgroundColor: '#4a9eff' }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } }
            });
        </script>
    @endif

    @if ($depreciationByYear->count() > 0)
        <script>
            new Chart(document.getElementById('depreciationChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($depreciationByYear->pluck('year')) !!},
                    datasets: [{ label: 'Depreciation (₱)', data: {!! json_encode($depreciationByYear->pluck('amount')) !!}, backgroundColor: '#0f2d5e' }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        </script>
    @endif

    @if ($costByQuarter->sum('amount') > 0)
        <script>
            new Chart(document.getElementById('costChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($costByQuarter->pluck('quarter')) !!},
                    datasets: [{ label: 'Cost (₱)', data: {!! json_encode($costByQuarter->pluck('amount')) !!}, backgroundColor: '#d97706' }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        </script>
    @endif
   
      @if ($totalAssetsCount > 0)
        <script>
            const statusColors = { active: '#4a9eff', in_maintenance: '#d97706', in_storage: '#94a3b8', disposed: '#e24b4a' };
            const statusLabels = {!! json_encode($statusCounts->keys()->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))) !!};
            const statusValues = {!! json_encode($statusCounts->values()) !!};
            const statusKeys = {!! json_encode($statusCounts->keys()) !!};

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: statusKeys.map(k => statusColors[k] || '#94a3b8')
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'right' } }
                }
            });
        </script>
    @endif

</x-prototype-layout>
