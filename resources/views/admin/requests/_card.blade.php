@php
    $priorityClass = match($wo->priority) {
        'critical' => 'b-crit',
        'high' => 'b-hi',
        'medium' => 'b-med',
        'low' => 'b-lo',
        default => '',
    };
    $statusClass = match($wo->status) {
        'pending' => 'b-pend',
        'assigned', 'in_progress' => 'b-prog',
        'completed' => 'b-done',
        'cancelled' => 'b-canc',
        default => '',
    };
    $statusLabel = match($wo->status) {
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'cancelled' => 'Rejected',
        default => ucfirst($wo->status),
    };
@endphp

<div class="card" style="margin-bottom:14px;border-left:3px solid {{ $wo->priority === 'critical' ? '#e24b4a' : ($wo->priority === 'high' ? '#f59e0b' : '#94a3b8') }}">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
        <div>
            <div style="font-size:11px;color:#94a3b8">{{ $wo->work_order_number }} · {{ $wo->reported_at->format('M j, Y') }}</div>
            <div style="font-weight:700;font-size:14.5px;color:#0f2d5e;margin-top:2px">{{ $wo->title }}</div>
        </div>
        <div style="display:flex;gap:5px">
            <span class="badge {{ $priorityClass }}">{{ ucfirst($wo->priority) }}</span>
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
    </div>

    <div style="font-size:12px;color:#64748b;margin-bottom:8px;display:flex;gap:12px;flex-wrap:wrap">
        <span><i class="ti ti-user" style="font-size:12px"></i> {{ $wo->requestedBy->first_name ?? '—' }} {{ $wo->requestedBy->last_name ?? '' }}</span>
        <span><i class="ti ti-package" style="font-size:12px"></i> {{ $wo->asset->asset_tag ?? '—' }} — {{ $wo->asset->name ?? '' }}</span>
        @if ($wo->assignedTo)
            <span><i class="ti ti-tool" style="font-size:12px"></i> {{ $wo->assignedTo->first_name }} {{ $wo->assignedTo->last_name }}</span>
        @endif
    </div>

    <div style="background:#f8fafc;border-radius:6px;padding:10px 12px;font-size:12.5px;color:#334155;margin-bottom:{{ $showActions ? '12px' : '0' }}">
        {{ $wo->description }}
    </div>

    @if ($showActions)
        <div class="fa">
            <button class="btn pri sm" onclick="document.getElementById('modal-approve-{{ $wo->id }}').style.display='flex'">
                <i class="ti ti-check" style="font-size:13px"></i> Approve & assign
            </button>
            <button class="btn sm" style="border-color:#e24b4a;color:#e24b4a" onclick="document.getElementById('modal-reject-{{ $wo->id }}').style.display='flex'">
                <i class="ti ti-x" style="font-size:13px"></i> Reject
            </button>
        </div>
    @endif
</div>
