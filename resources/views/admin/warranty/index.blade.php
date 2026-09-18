<x-prototype-layout title="Warranty tracking">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Lifecycle</div>
            <div class="pt">Warranty tracking</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px">
        <div class="card" style="text-align:center">
            <div style="font-size:24px;font-weight:700;color:#e24b4a">{{ $expired->count() }}</div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;font-weight:600">Expired</div>
        </div>
        <div class="card" style="text-align:center">
            <div style="font-size:24px;font-weight:700;color:#d97706">{{ $expiringSoon->count() }}</div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;font-weight:600">Expiring within 30 days</div>
        </div>
        <div class="card" style="text-align:center">
            <div style="font-size:24px;font-weight:700;color:#2d7d32">{{ $safe->count() }}</div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;font-weight:600">Safe</div>
        </div>
    </div>

    <div class="tabs" id="warranty-tabs">
        <div class="tab active" data-group="expired" onclick="showWarrantyGroup('expired')">Expired ({{ $expired->count() }})</div>
        <div class="tab" data-group="expiring" onclick="showWarrantyGroup('expiring')">Expiring Soon ({{ $expiringSoon->count() }})</div>
        <div class="tab" data-group="all" onclick="showWarrantyGroup('all')">All Tracked ({{ $trackedAssets->count() }})</div>
    </div>

    @php
        $renderRow = function ($asset) {
            $daysDiff = now()->startOfDay()->diffInDays($asset->warranty_expiry_date, false);
            $label = $daysDiff < 0
                ? abs($daysDiff) . ' ' . \Illuminate\Support\Str::plural('day', abs($daysDiff)) . ' ago'
                : 'in ' . $daysDiff . ' ' . \Illuminate\Support\Str::plural('day', $daysDiff);
            return [$daysDiff, $label];
        };
    @endphp

    <div id="warranty-group-expired">
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Expired</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expired as $asset)
                        @php [$days, $label] = $renderRow($asset); @endphp
                        <tr>
                            <td>{{ $asset->asset_tag }} — {{ $asset->name }}</td>
                            <td>{{ $asset->category->name ?? '—' }}</td>
                            <td>{{ $asset->location->name ?? '—' }}</td>
                            <td>
                                {{ $asset->warranty_expiry_date->format('M j, Y') }}
                                <span style="color:#e24b4a;font-size:11px">({{ $label }})</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:#64748b;padding:24px">No expired warranties.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="warranty-group-expiring" style="display:none">
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expiringSoon as $asset)
                        @php [$days, $label] = $renderRow($asset); @endphp
                        <tr>
                            <td>{{ $asset->asset_tag }} — {{ $asset->name }}</td>
                            <td>{{ $asset->category->name ?? '—' }}</td>
                            <td>{{ $asset->location->name ?? '—' }}</td>
                            <td>
                                {{ $asset->warranty_expiry_date->format('M j, Y') }}
                                <span style="color:#d97706;font-size:11px">({{ $label }})</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:#64748b;padding:24px">Nothing expiring in the next 30 days.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="warranty-group-all" style="display:none">
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Warranty expiry</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trackedAssets as $asset)
                        @php
                            [$days, $label] = $renderRow($asset);
                            $statusColor = $days < 0 ? '#e24b4a' : ($days <= 30 ? '#d97706' : '#2d7d32');
                            $statusText = $days < 0 ? 'Expired' : ($days <= 30 ? 'Expiring soon' : 'Safe');
                        @endphp
                        <tr>
                            <td>{{ $asset->asset_tag }} — {{ $asset->name }}</td>
                            <td>{{ $asset->category->name ?? '—' }}</td>
                            <td>{{ $asset->location->name ?? '—' }}</td>
                            <td>{{ $asset->warranty_expiry_date->format('M j, Y') }}</td>
                            <td><span style="color:{{ $statusColor }};font-weight:600;font-size:12px">{{ $statusText }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:#64748b;padding:24px">No assets have a warranty expiry date recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showWarrantyGroup(group) {
            document.querySelectorAll('#warranty-tabs .tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`#warranty-tabs [data-group="${group}"]`).classList.add('active');
            document.getElementById('warranty-group-expired').style.display = group === 'expired' ? '' : 'none';
            document.getElementById('warranty-group-expiring').style.display = group === 'expiring' ? '' : 'none';
            document.getElementById('warranty-group-all').style.display = group === 'all' ? '' : 'none';
        }
    </script>

</x-prototype-layout>
