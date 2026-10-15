<x-prototype-layout title="My dashboard">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Holder</div>
            <div class="pt">My dashboard</div>
        </div>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    @if ($pendingAssignments->count() > 0)
        <div class="card" style="background:#fffbeb;border-color:#fde68a;margin-bottom:18px">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#7a4a0a;margin-bottom:6px">
                <i class="ti ti-clipboard-check" style="font-size:16px"></i>
                Action required — asset acknowledgement pending
            </div>
            <div style="font-size:12.5px;color:#7a4a0a;margin-bottom:14px">
                You have {{ $pendingAssignments->count() }} asset{{ $pendingAssignments->count() > 1 ? 's' : '' }} that require{{ $pendingAssignments->count() > 1 ? '' : 's' }} your confirmation of receipt. Please acknowledge that you have physically received and accepted custody.
            </div>

            @foreach ($pendingAssignments as $assignment)
                <div style="background:#fff;border:0.5px solid #e5e9f0;border-radius:8px;padding:14px;display:flex;align-items:center;justify-content:space-between;margin-bottom:{{ !$loop->last ? '10px' : '0' }}">
                    <div>
                        <div style="font-weight:600;font-size:13.5px;color:#0f2d5e">{{ $assignment->asset->name }}</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">
                            {{ $assignment->asset->asset_tag }} · {{ $assignment->asset->category->name ?? 'Uncategorised' }} · Assigned {{ $assignment->assigned_at->format('M j, Y') }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('holder.assignments.acknowledge', $assignment) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn pri sm">
                            <i class="ti ti-check" style="font-size:13px"></i> Acknowledge receipt
                        </button>
                    </form>
                </div>
            @endforeach

            <div style="font-size:11.5px;color:#7a4a0a;margin-top:10px;display:flex;align-items:center;gap:6px">
                <i class="ti ti-info-circle" style="font-size:13px"></i>
                By acknowledging, you confirm you have received this asset and accept responsibility for its care and proper use.
            </div>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px">
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-package" style="font-size:13px"></i> My assets</div>
            <div style="font-size:26px;font-weight:700;color:#0f2d5e">{{ $myAssets->count() }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">Confirmed in your custody</div>
        </div>
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-clipboard-check" style="font-size:13px"></i> Awaiting acknowledgement</div>
            <div style="font-size:26px;font-weight:700;color:#7a4a0a">{{ $pendingAssignments->count() }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">Assets to confirm receipt of</div>
        </div>
        <div class="card">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-tool" style="font-size:13px"></i> Open requests</div>
            <div style="font-size:26px;font-weight:700;color:#185fa5">{{ $openRequests }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">Issues being handled</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:18px">
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e">Recent requests</div>
<a href="{{ url('/holder/requests') }}" style="font-size:12px;color:#185fa5">View all</a>
</div>
@forelse ($recentRequests as $wo)
<div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-top:0.5px solid #e5e9f0">
<div>
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $wo->asset->name ?? 'Asset' }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $wo->work_order_number ?? ('#'.$wo->id) }} · {{ \Carbon\Carbon::parse($wo->reported_at ?? $wo->created_at)->format('M j, Y') }}</div>
</div>
<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#f1f5f9;color:#334155">{{ ucfirst(str_replace('_',' ',$wo->status)) }}</span>
</div>
@empty
<div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No requests yet.</div>
@endforelse
</div>
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e">Latest notifications</div>
<a href="{{ route('notifications.index') }}" style="font-size:12px;color:#185fa5">View all</a>
</div>
@forelse ($recentNotifications as $n)
<div style="padding:9px 0 9px 9px;border-top:0.5px solid #e5e9f0;{{ $n->is_read ? '' : 'border-left:3px solid #185fa5;' }}">
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $n->title ?? 'Notification' }}</div>
<div style="font-size:11.5px;color:#64748b">{{ \Illuminate\Support\Str::limit($n->body ?? $n->message ?? '', 70) }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:2px">{{ $n->created_at->diffForHumans() }}</div>
</div>
@empty
<div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No notifications.</div>
@endforelse
</div>
</div>
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e">My assets</div>
<a href="{{ url('/holder/assets') }}" style="font-size:12px;color:#185fa5">View all</a>
</div>
@forelse ($myAssets as $assignment)
<a href="{{ route('holder.assets.show', $assignment) }}" style="display:flex;align-items:center;gap:12px;padding:9px 0;border-top:0.5px solid #e5e9f0;text-decoration:none">
@if ($assignment->asset->primaryPhoto)
<img src="{{ $assignment->asset->primaryPhoto->url() }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;flex-shrink:0">
@else
<div style="width:40px;height:40px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;flex-shrink:0"><i class="ti ti-photo"></i></div>
@endif
<div>
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $assignment->asset->name }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $assignment->asset->asset_tag }} · {{ $assignment->asset->category->name ?? 'Uncategorised' }}</div>
</div>
</a>
@empty
<div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No assets acknowledged yet.</div>
@endforelse
</div>


</x-prototype-layout>
