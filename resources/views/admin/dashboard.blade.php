<x-prototype-layout title="Administrative Admin">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Administrative Admin</div>
            <div class="pt">Dashboard overview</div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('admin.reports.index') }}" class="btn sm"><i class="ti ti-chart-bar" style="font-size:13px"></i> Reports</a>
            <a href="{{ route('admin.assets.create') }}" class="btn pri sm"><i class="ti ti-plus" style="font-size:13px"></i> Add Asset</a>
        </div>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    @include('partials.pending-assignments-banner')

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px">
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-package" style="font-size:13px"></i> Total assets</div>
            <div style="font-size:26px;font-weight:700;color:#0f2d5e">{{ number_format($totalAssets) }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">{{ $newThisMonth }} this month</div>
        </div>
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-circle-check" style="font-size:13px"></i> Active assets</div>
            <div style="font-size:26px;font-weight:700;color:#2d7d32">{{ number_format($activeAssets) }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">{{ $activePercent }}% of total</div>
        </div>
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-arrows-exchange" style="font-size:13px"></i> Pending transfers</div>
            <div style="font-size:26px;font-weight:700;color:#7a4a0a">{{ $pendingTransfersCount }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">Need approval</div>
        </div>
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-trash" style="font-size:13px"></i> Disposed assets</div>
            <div style="font-size:26px;font-weight:700;color:#0f2d5e">{{ $disposedThisYear }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">This fiscal year</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:18px">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                <div style="font-size:14px;font-weight:700;color:#0f2d5e">Asset acquisition ({{ now()->year }})</div>
                <span style="font-size:11px;color:#94a3b8">Monthly overview</span>
            </div>
            <canvas id="acquisitionChart" style="max-height:220px"></canvas>
        </div>

        <div class="card">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:14px">Department distribution</div>
            @if ($byDepartment->count() > 0)
                <canvas id="deptChart" style="max-height:200px"></canvas>
            @else
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No department data yet.</div>
            @endif
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:18px">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                <div style="font-size:14px;font-weight:700;color:#0f2d5e">Recent registrations</div>
                <a href="{{ route('admin.assets.index') }}" class="btn sm">View all</a>
            </div>
            <table style="width:100%">
                <thead>
                    <tr style="text-align:left;font-size:10.5px;text-transform:uppercase;color:#94a3b8">
                        <th style="padding-bottom:8px">Asset name</th>
                        <th style="padding-bottom:8px">Type</th>
                        <th style="padding-bottom:8px">Department</th>
                        <th style="padding-bottom:8px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentAssets as $asset)
                        <tr style="border-top:0.5px solid #f1f5f9;font-size:12.5px">
                            <td style="padding:8px 0">{{ $asset->name }}</td>
                            <td style="padding:8px 0;color:#64748b">{{ $asset->category->name ?? '—' }}</td>
                            <td style="padding:8px 0;color:#64748b">{{ $asset->location->name ?? '—' }}</td>
                            <td style="padding:8px 0">
                                <span class="badge {{ $asset->status === 'active' ? 'b-act' : ($asset->status === 'in_maintenance' ? 'b-prog' : 'b-pend') }}">
                                    {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">No assets registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:14px">Inventory alerts</div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:12.5px">
                <span><i class="ti ti-user-x" style="font-size:13px;color:#e24b4a"></i> <strong>{{ $unassignedCount }}</strong> assets unassigned — no holder designated</span>
                <a href="#unassigned-panel" class="btn sm" style="padding:4px 10px">View</a>
            </div>
            <div style="margin-bottom:12px;font-size:12.5px">
                <i class="ti ti-clock" style="font-size:13px;color:#d97706"></i> <strong>{{ $pendingAcknowledgementsCount }}</strong> acknowledgement{{ $pendingAcknowledgementsCount !== 1 ? 's' : '' }} pending from asset holders
            </div>
            <div style="margin-bottom:12px;font-size:12.5px">
                <i class="ti ti-alert-triangle" style="font-size:13px;color:#d97706"></i> <strong>{{ $maintenanceDueThisWeek }}</strong> assets due for maintenance this week
            </div>
            <div style="margin-bottom:12px;font-size:12.5px">
                <i class="ti ti-arrows-exchange" style="font-size:13px;color:#4a9eff"></i> <strong>{{ $pendingTransfersCount }}</strong> transfers pending approval
            </div>
            <div style="font-size:12.5px">
                <i class="ti ti-file-text" style="font-size:13px;color:#94a3b8"></i> <strong>{{ $warrantyExpiringSoon }}</strong> assets warranty expiring soon
            </div>
        </div>
    </div>

    @if ($unassignedCount > 0)
        <div class="card" id="unassigned-panel" style="border-left:3px solid #e24b4a">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <div style="font-size:14px;font-weight:700;color:#a32d2d"><i class="ti ti-user-x" style="font-size:15px"></i> Unassigned assets — requires action</div>
                <a href="{{ route('admin.assets.index') }}" class="btn sm">View all in inventory</a>
            </div>
            <div style="font-size:12px;color:#64748b;margin-bottom:16px">
                The following assets have no designated asset holder. Assign them to a staff member to establish proper custody and accountability.
            </div>

            @foreach ($unassignedAssets as $asset)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-top:0.5px solid #f1f5f9">
                    <div>
                        <div style="font-weight:600;font-size:13px;color:#0f2d5e">{{ $asset->name }}</div>
                        <div style="font-size:11.5px;color:#94a3b8">{{ $asset->asset_tag }} · {{ $asset->category->name ?? '—' }} · {{ $asset->location->name ?? '—' }}</div>
                    </div>
                    <button class="btn sm" onclick="document.getElementById('modal-assign-{{ $asset->id }}').style.display='flex'">
                        <i class="ti ti-user-plus" style="font-size:13px"></i> Assign holder
                    </button>
                </div>

                <div id="modal-assign-{{ $asset->id }}" class="modal-overlay">
                    <div class="modal-box">
                        <div class="modal-hdr">
                            <div class="modal-ttl">Assign holder — {{ $asset->name }}</div>
                            <button class="modal-close" onclick="document.getElementById('modal-assign-{{ $asset->id }}').style.display='none'">&times;</button>
                        </div>
                        <form method="POST" action="{{ route('admin.assets.assignHolder', $asset) }}">
                            @csrf
                            <div class="fg">
                                <label>Assign to *</label>
                                <select name="holder_id" required>
                                    <option value="">Select user</option>
                                    @foreach (\App\Models\User::where('is_active', true)->orderBy('first_name')->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ \Illuminate\Support\Str::headline($user->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="fa">
                                <button type="submit" class="btn pri sm">Assign</button>
                                <button type="button" class="btn sm" onclick="document.getElementById('modal-assign-{{ $asset->id }}').style.display='none'">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <script>
        new Chart(document.getElementById('acquisitionChart'), {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{ label: 'Assets acquired', data: {!! json_encode($monthlyAcquisitions) !!}, backgroundColor: '#4a9eff' }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        @if ($byDepartment->count() > 0)
            new Chart(document.getElementById('deptChart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($byDepartment->pluck('name')) !!},
                    datasets: [{
                        data: {!! json_encode($byDepartment->pluck('count')) !!},
                        backgroundColor: ['#4a9eff','#0f2d5e','#94a3b8','#d97706','#22c55e','#a855f7']
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10.5 } } } } }
            });
        @endif
    </script>

</x-prototype-layout>
