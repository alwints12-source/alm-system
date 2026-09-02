<x-prototype-layout title="SLA policies">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">SLA & compliance</div>
        </div>
        <a href="{{ route('admin.sla-policies.create') }}" class="btn pri sm">
            <i class="ti ti-plus" style="font-size:13px"></i> New policy
        </a>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    {{-- ============ RESPONSE SECTION ============ --}}
    <div style="font-size:13px;font-weight:700;color:#0f2d5e;margin-bottom:8px;display:flex;align-items:center;gap:6px">
        <i class="ti ti-bell-ringing" style="font-size:15px"></i> Response SLA
        <span style="font-size:11px;font-weight:400;color:#94a3b8">— how quickly requests get approved & assigned</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px">
        <div class="card" style="text-align:center">
            <div style="font-size:22px;font-weight:700;color:#0f2d5e">{{ $responseTotalTracked }}</div>
            <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600">Total tracked</div>
        </div>
        <div class="card" style="text-align:center">
            <div style="font-size:22px;font-weight:700;color:#2d7d32">{{ $responseTotalMet }}</div>
            <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600">Met</div>
        </div>
        <div class="card" style="text-align:center">
            <div style="font-size:22px;font-weight:700;color:#e24b4a">{{ $responseTotalBreached }}</div>
            <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600">Breached</div>
        </div>
        <div class="card" style="text-align:center">
            <div style="font-size:22px;font-weight:700;color:#0f2d5e">{{ $responseComplianceRate }}%</div>
            <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600">Compliance</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px">
        @if ($responseTotalTracked > 0)
            <canvas id="responseChart" style="max-height:220px"></canvas>
        @else
            <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No response data yet.</div>
        @endif
    </div>

    {{-- ============ RESOLUTION SECTION ============ --}}
    <div style="font-size:13px;font-weight:700;color:#0f2d5e;margin-bottom:8px;display:flex;align-items:center;gap:6px">
        <i class="ti ti-checkbox" style="font-size:15px"></i> Resolution SLA
        <span style="font-size:11px;font-weight:400;color:#94a3b8">— how quickly assigned work actually gets finished (completed work orders only)</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px">
        <div class="card" style="text-align:center">
            <div style="font-size:22px;font-weight:700;color:#0f2d5e">{{ $resolutionTotalTracked }}</div>
            <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600">Total completed</div>
        </div>
        <div class="card" style="text-align:center">
            <div style="font-size:22px;font-weight:700;color:#2d7d32">{{ $resolutionTotalMet }}</div>
            <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600">Met</div>
        </div>
        <div class="card" style="text-align:center">
            <div style="font-size:22px;font-weight:700;color:#e24b4a">{{ $resolutionTotalBreached }}</div>
            <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600">Breached</div>
        </div>
        <div class="card" style="text-align:center">
            <div style="font-size:22px;font-weight:700;color:#0f2d5e">{{ $resolutionComplianceRate }}%</div>
            <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600">Compliance</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px">
        @if ($resolutionTotalTracked > 0)
            <canvas id="resolutionChart" style="max-height:220px"></canvas>
        @else
            <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No completed, SLA-tracked work orders yet.</div>
        @endif
    </div>

    {{-- Technician resolution compliance chart --}}
    @if (count($technicianStats) > 0)
        <div class="card" style="margin-bottom:24px">
            <div style="font-size:13px;font-weight:600;color:#0f2d5e;margin-bottom:4px">Resolution SLA by technician</div>
            <div style="font-size:11.5px;color:#94a3b8;margin-bottom:12px">Click a bar to see that technician's individual work orders</div>
            <canvas id="techChart" style="max-height:260px"></canvas>

            @foreach ($technicianStats as $index => $stat)
                <div id="tech-drilldown-{{ $index }}" class="tech-drilldown" style="display:none;margin-top:16px;border-top:1px solid #e5e9f0;padding-top:14px">
                    <div style="font-size:12.5px;font-weight:600;color:#0f2d5e;margin-bottom:10px">
                        {{ $stat['technician']->first_name }} {{ $stat['technician']->last_name }} — {{ $stat['met'] }}/{{ $stat['total'] }} resolved within SLA
                    </div>
                    @foreach ($stat['orders'] as $result)
                        @php $wo = $result['work_order']; @endphp
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:0.5px solid #f1f5f9;font-size:12px">
                            <div>
                                <strong>{{ $wo->work_order_number }}</strong> — {{ $wo->title }}
                                <span style="color:#94a3b8;font-size:11px">({{ $wo->asset->asset_tag ?? '' }})</span>
                            </div>
                            @if ($result['met'])
                                <span class="badge b-act" style="font-size:10px">Met</span>
                            @else
                                <span class="badge b-canc" style="font-size:10px">Breached</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent breaches --}}
    @if ($recentBreaches->count() > 0)
        <div class="card" style="margin-bottom:24px">
            <div style="font-size:13px;font-weight:600;color:#0f2d5e;margin-bottom:12px">Recent breaches</div>
            @foreach ($recentBreaches as $wo)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:0.5px solid #f1f5f9;font-size:12.5px">
                    <div>
                        <strong>{{ $wo->work_order_number }}</strong> — {{ $wo->title }}
                        <div style="color:#94a3b8;font-size:11px">{{ $wo->asset->asset_tag ?? '' }} · Reported by {{ $wo->requestedBy->first_name ?? '' }} {{ $wo->requestedBy->last_name ?? '' }}</div>
                    </div>
                    <span style="color:#e24b4a;font-size:11px">{{ $wo->sla_breached_at?->format('M j, g:i A') }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div style="background:#e3f2fd;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12.5px;color:#185fa5">
        <i class="ti ti-info-circle" style="font-size:14px"></i>
        Policies below define the targets. Live countdowns appear on individual requests in Requests and Work Orders once approved.
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th style="width:160px">Policy name</th>
                    <th style="width:100px">Priority</th>
                    <th style="width:110px">Type</th>
                    <th style="width:130px">Category</th>
                    <th style="width:90px">Response</th>
                    <th style="width:90px">Resolution</th>
                    <th style="width:80px">Status</th>
                    <th style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($policies as $policy)
                    @php
                        $priorityClass = match($policy->priority) {
                            'critical' => 'b-crit', 'high' => 'b-hi', 'medium' => 'b-med', 'low' => 'b-lo', default => '',
                        };
                    @endphp
                    <tr>
                        <td>{{ $policy->name }}</td>
                        <td><span class="badge {{ $priorityClass }}">{{ ucfirst($policy->priority) }}</span></td>
                        <td>{{ $policy->maintenance_type ? ucfirst($policy->maintenance_type) : 'Any' }}</td>
                        <td>{{ $policy->category->name ?? 'Any' }}</td>
                        <td>{{ $policy->response_time_hours }}h</td>
                        <td>{{ $policy->resolution_time_hours }}h</td>
                        <td>
                            @if ($policy->is_active)
                                <span class="badge b-act">Active</span>
                            @else
                                <span class="badge b-canc">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="ab">
                                <a href="{{ route('admin.sla-policies.edit', $policy) }}" class="ac v">Edit</a>
                                <form method="POST" action="{{ route('admin.sla-policies.toggle', $policy) }}" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ac {{ $policy->is_active ? 'd' : 'e' }}">
                                        {{ $policy->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;color:#64748b;padding:24px">
                            No SLA policies yet. Click "New policy" to define your first response/resolution target.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($responseTotalTracked > 0)
        <script>
            new Chart(document.getElementById('responseChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_map(fn($p) => ucfirst($p['priority']), $responsePriorityBreakdown)) !!},
                    datasets: [
                        { label: 'Met', data: {!! json_encode(array_column($responsePriorityBreakdown, 'met')) !!}, backgroundColor: '#22c55e' },
                        { label: 'Breached', data: {!! json_encode(array_column($responsePriorityBreakdown, 'breached')) !!}, backgroundColor: '#e24b4a' }
                    ]
                },
                options: {
                    responsive: true,
                    scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        </script>
    @endif

    @if ($resolutionTotalTracked > 0)
        <script>
            new Chart(document.getElementById('resolutionChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_map(fn($p) => ucfirst($p['priority']), $resolutionPriorityBreakdown)) !!},
                    datasets: [
                        { label: 'Met', data: {!! json_encode(array_column($resolutionPriorityBreakdown, 'met')) !!}, backgroundColor: '#22c55e' },
                        { label: 'Breached', data: {!! json_encode(array_column($resolutionPriorityBreakdown, 'breached')) !!}, backgroundColor: '#e24b4a' }
                    ]
                },
                options: {
                    responsive: true,
                    scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        </script>
    @endif

    @if (count($technicianStats) > 0)
        <script>
            const techChart = new Chart(document.getElementById('techChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_map(fn($s) => $s['technician']->first_name . ' ' . $s['technician']->last_name, $technicianStats)) !!},
                    datasets: [{
                        label: 'Resolution compliance %',
                        data: {!! json_encode(array_column($technicianStats, 'rate')) !!},
                        backgroundColor: {!! json_encode(array_map(fn($s) => $s['rate'] >= 80 ? '#22c55e' : ($s['rate'] >= 50 ? '#f59e0b' : '#e24b4a'), $technicianStats)) !!}
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } },
                    plugins: { legend: { display: false } },
                    onClick: (evt, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            document.querySelectorAll('.tech-drilldown').forEach(el => el.style.display = 'none');
                            const target = document.getElementById('tech-drilldown-' + index);
                            target.style.display = 'block';
                            target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }
                }
            });
        </script>
    @endif

</x-prototype-layout>
