<x-prototype-layout title="Asset conditions">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">Asset conditions</div>
        </div>
    </div>

    <div style="background:#e3f2fd;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12.5px;color:#185fa5;display:flex;align-items:center;gap:8px">
        <i class="ti ti-info-circle" style="font-size:15px"></i>
        Condition is read-only here. It only changes when a Technician completes a work order — this keeps every condition change tied to an actual resolved issue.
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th style="width:80px">Tag</th>
                    <th style="width:160px">Asset name</th>
                    <th style="width:100px">Condition</th>
                    <th style="width:130px">Last serviced</th>
                    <th style="width:80px">Repairs</th>
                    <th style="width:100px">Total cost (₱)</th>
                    <th style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assets as $asset)
                    @php
                        $conditionClass = match($asset->condition) {
                            'excellent', 'good' => 'b-act',
                            'fair' => 'b-pend',
                            'poor', 'unserviceable' => 'b-canc',
                            default => '',
                        };
                        $lastServiced = $asset->workOrders->where('status', 'completed')->sortByDesc('completed_at')->first();
                        $repairCount = $asset->workOrders->where('status', 'completed')->count();
                        $totalCost = $asset->workOrders->where('status', 'completed')->sum('actual_cost');
                    @endphp
                    <tr>
                        <td>{{ $asset->asset_tag }}</td>
                        <td>{{ $asset->name }}</td>
                        <td><span class="badge {{ $conditionClass }}" style="text-transform:capitalize">{{ $asset->condition }}</span></td>
                        <td>{{ $lastServiced?->completed_at->format('M j, Y') ?? '—' }}</td>
                        <td>{{ $repairCount }}</td>
                        <td>{{ $totalCost > 0 ? number_format($totalCost, 0) : '—' }}</td>
                        <td>
                            @if ($repairCount > 0)
                                <button class="ac v" onclick="document.getElementById('history-{{ $asset->id }}').style.display='flex'">History</button>
                            @else
                                <span style="font-size:11px;color:#94a3b8">No history</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:#64748b;padding:24px">No assets registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- History modals — one per asset with completed work orders --}}
    @foreach ($assets as $asset)
        @php $completed = $asset->workOrders->where('status', 'completed')->sortByDesc('completed_at'); @endphp
        @if ($completed->count() > 0)
            <div id="history-{{ $asset->id }}" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-hdr">
                        <div class="modal-ttl">Condition history — {{ $asset->asset_tag }}</div>
                        <button class="modal-close" onclick="document.getElementById('history-{{ $asset->id }}').style.display='none'">&times;</button>
                    </div>

                    @foreach ($completed as $wo)
                        <div style="border-left:2px solid #4a9eff;padding-left:12px;margin-bottom:14px">
                            <div style="font-size:12px;color:#94a3b8">{{ $wo->completed_at->format('M j, Y g:i A') }}</div>
                            <div style="font-weight:600;font-size:13px;color:#0f2d5e;margin:2px 0">{{ $wo->title }}</div>
                            <div style="font-size:12px;color:#64748b;margin-bottom:4px">
                                By {{ $wo->assignedTo->first_name ?? '' }} {{ $wo->assignedTo->last_name ?? '' }}
                                @if ($wo->actual_cost) · ₱{{ number_format($wo->actual_cost, 0) }} @endif
                            </div>
                            <div style="font-size:12.5px;color:#334155">{{ $wo->resolution_notes }}</div>
                        </div>
                    @endforeach

                    <div class="fa">
                        <button type="button" class="btn sm" onclick="document.getElementById('history-{{ $asset->id }}').style.display='none'">Close</button>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

</x-prototype-layout>
