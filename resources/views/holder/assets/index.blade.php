<x-prototype-layout title="My assets">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Holder</div>
            <div class="pt">My assets</div>
        </div>
    </div>

    @if ($assignments->count() === 0)
        <div class="card" style="text-align:center;color:#64748b;padding:32px">
            No confirmed assets yet. Once you acknowledge a pending asset from your dashboard, it will appear here.
        </div>
    @else
        @foreach ($assignments as $assignment)
            @php $asset = $assignment->asset; @endphp
            <div class="card" style="margin-bottom:14px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <div>
                        <div style="font-weight:700;font-size:15px;color:#0f2d5e">{{ $asset->name }}</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">
                            {{ $asset->asset_tag }} · {{ $asset->category->name ?? 'Uncategorised' }}
                        </div>
                    </div>
                    <span class="badge b-act">Active</span>
                </div>

                <div class="fg2" style="margin-bottom:0">
                    <div>
                        <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Location</div>
                        <div style="font-size:13px">{{ $asset->location->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Assigned date</div>
                        <div style="font-size:13px">{{ $assignment->assigned_at->format('M j, Y') }}</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Serial no.</div>
                        <div style="font-size:13px">{{ $asset->serial_number ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Warranty until</div>
                        <div style="font-size:13px">{{ $asset->warranty_expiry_date?->format('M j, Y') ?? '—' }}</div>
                    </div>
                </div>

                <div style="margin-top:14px;display:flex;gap:8px">
                    <button class="btn sm" disabled title="Coming in a later sprint">View full details</button>
                    <button class="btn sm" disabled title="Coming in a later sprint">Request maintenance</button>
                    <button class="btn sm" disabled title="Coming in a later sprint">Report issue</button>
                </div>
            </div>
        @endforeach
    @endif

</x-prototype-layout>
