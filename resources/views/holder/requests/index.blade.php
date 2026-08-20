<x-prototype-layout title="My requests">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Holder</div>
            <div class="pt">My requests</div>
        </div>
    </div>

    @if ($requests->count() === 0)
        <div class="card" style="text-align:center;color:#64748b;padding:32px">
            You haven't reported any issues yet. Go to My Assets to report a problem.
        </div>
    @else
        @foreach ($requests as $wo)
            @php
                $statusClass = match($wo->status) {
                    'pending' => 'b-pend',
                    'assigned', 'in_progress' => 'b-prog',
                    'completed' => 'b-act',
                    'cancelled' => 'b-canc',
                    default => '',
                };
                $statusLabel = match($wo->status) {
                    'pending' => 'Awaiting review',
                    'assigned' => 'Assigned',
                    'in_progress' => 'In progress',
                    'completed' => 'Resolved',
                    'cancelled' => 'Rejected',
                    default => ucfirst($wo->status),
                };
            @endphp
            <div class="card" style="margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                    <div>
                        <div style="font-size:11px;color:#94a3b8">{{ $wo->work_order_number }} · {{ $wo->reported_at->format('M j, Y') }}</div>
                        <div style="font-weight:700;font-size:14.5px;color:#0f2d5e;margin-top:2px">{{ $wo->title }}</div>
                    </div>
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                <div style="font-size:12px;color:#64748b;margin-bottom:10px">
                    {{ $wo->asset->asset_tag ?? '—' }} — {{ $wo->asset->name ?? '' }}
                </div>

                <div style="background:#f8fafc;border-radius:6px;padding:10px 12px;font-size:12.5px;color:#334155;margin-bottom:{{ $wo->status === 'completed' || $wo->status === 'cancelled' ? '10px' : '0' }}">
                    {{ $wo->description }}
                </div>

                @if ($wo->status === 'assigned' || $wo->status === 'in_progress')
                    <div style="font-size:12px;color:#64748b;margin-top:10px">
                        <i class="ti ti-tool" style="font-size:12px"></i> Assigned to {{ $wo->assignedTo->first_name ?? '' }} {{ $wo->assignedTo->last_name ?? '' }}
                        @if ($wo->due_date)
                            · Scheduled {{ $wo->due_date->format('M j, Y g:i A') }}
                        @endif
                    </div>
                @endif

                @if ($wo->status === 'completed')
                    <div style="background:#f0fdf4;border:1px solid #c0dd97;border-radius:6px;padding:10px 12px;font-size:12.5px;color:#2d7d32">
                        <strong>Resolution notes:</strong> {{ $wo->resolution_notes }}
                        <div style="font-size:11px;color:#5a8a5a;margin-top:4px">
                            Resolved by {{ $wo->assignedTo->first_name ?? '' }} {{ $wo->assignedTo->last_name ?? '' }} on {{ $wo->completed_at->format('M j, Y g:i A') }}
                        </div>
                    </div>
                @endif

                @if ($wo->status === 'cancelled' && $wo->resolution_notes)
                    <div style="background:#fff0f0;border:1px solid #fbc5c5;border-radius:6px;padding:10px 12px;font-size:12.5px;color:#a32d2d">
                        <strong>Reason for rejection:</strong> {{ $wo->resolution_notes }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif

</x-prototype-layout>
