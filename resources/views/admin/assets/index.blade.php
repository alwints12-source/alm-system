<x-prototype-layout title="Asset inventory">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Administrative Admin</div>
            <div class="pt">Asset inventory</div>
        </div>
        <a href="{{ route('admin.assets.create') }}" class="btn pri sm">
            <i class="ti ti-plus" style="font-size:13px"></i> Add asset
        </a>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    <div class="fr">
        <input class="fs" type="text" placeholder="Search by name, tag, serial number..." id="asset-search" onkeyup="filterAssets()">
        <select id="status-filter" onchange="filterAssets()">
            <option value="">All status</option>
            <option value="active">Active</option>
            <option value="in_storage">In storage</option>
            <option value="in_maintenance">Under repair</option>
            <option value="disposed">Disposed</option>
        </select>
        <select id="assign-filter" onchange="filterAssets()" style="border-color:#f59e0b;color:#7a4a0a;font-weight:500">
            <option value="">All assignments</option>
            <option value="unassigned">⚠ Unassigned only</option>
            <option value="assigned">Assigned only</option>
        </select>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th style="width:80px">Tag</th>
                    <th style="width:160px">Asset name</th>
                    <th style="width:130px">Type</th>
                    <th style="width:120px">Location</th>
                    <th style="width:140px">Assigned to</th>
                    <th style="width:90px">Ack. status</th>
                    <th style="width:90px">Status</th>
                    <th style="width:90px">Value (₱)</th>
                    <th style="width:80px">Actions</th>
                </tr>
            </thead>
            <tbody id="assets-table-body">
                @forelse ($assets as $asset)
                    @php
                        $statusClass = match($asset->status) {
                            'active' => 'b-act',
                            'in_maintenance' => 'b-maint',
                            'in_storage' => 'b-pend',
                            'disposed' => 'b-canc',
                            default => '',
                        };
                        $statusLabel = match($asset->status) {
                            'active' => 'Active',
                            'in_maintenance' => 'Under repair',
                            'in_storage' => 'In storage',
                            'disposed' => 'Disposed',
                            default => ucfirst($asset->status),
                        };
                        $assignment = $asset->currentAssignment;
                        $isAssigned = $assignment !== null;
                    @endphp
                    <tr data-status="{{ $asset->status }}" data-assigned="{{ $isAssigned ? 'assigned' : 'unassigned' }}" data-search="{{ strtolower($asset->name . ' ' . $asset->asset_tag . ' ' . $asset->serial_number) }}">
                        <td>{{ $asset->asset_tag }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '—' }}</td>
                        <td>{{ $asset->location->name ?? '—' }}</td>
                        <td>
                            @if ($isAssigned)
                                @php
                                    $holder = $assignment->holder;
                                    $initials = strtoupper(substr($holder->first_name, 0, 1) . substr($holder->last_name, 0, 1));
                                @endphp
                                <div style="display:flex;align-items:center;gap:6px">
                                    <div class="ava ava-sm">{{ $initials }}</div>
                                    <span style="font-size:12px">{{ $holder->first_name }} {{ $holder->last_name }}</span>
                                </div>
                            @else
                                <span style="font-size:11.5px;color:#854f0b;background:#fffbeb;padding:2px 8px;border-radius:20px;font-weight:500;border:0.5px solid #fde68a">⚠ Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @if (!$isAssigned)
                                <span class="badge b-canc" style="font-size:10.5px">N/A</span>
                            @elseif ($assignment->status === 'acknowledged')
                                <span class="badge b-act" style="font-size:10.5px">Confirmed</span>
                            @else
                                <span class="badge b-pend" style="font-size:10.5px">Pending</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td>{{ number_format($asset->acquisition_cost, 0) }}</td>
                        <td>
                            <div class="ab">
                                <button class="ac v" disabled title="Detail view coming soon">View</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:#64748b;padding:24px">
                            No assets registered yet. Click "Add asset" to register the first one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function filterAssets() {
            const search = document.getElementById('asset-search').value.toLowerCase();
            const status = document.getElementById('status-filter').value;
            const assign = document.getElementById('assign-filter').value;
            const rows = document.querySelectorAll('#assets-table-body tr[data-search]');

            rows.forEach(row => {
                const matchesSearch = row.dataset.search.includes(search);
                const matchesStatus = !status || row.dataset.status === status;
                const matchesAssign = !assign || row.dataset.assigned === assign;
                row.style.display = (matchesSearch && matchesStatus && matchesAssign) ? '' : 'none';
            });
        }
    </script>

</x-prototype-layout>
