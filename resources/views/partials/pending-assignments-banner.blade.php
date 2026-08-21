@if ($pendingAssignments->count() > 0)
    <div class="card" style="background:#fffbeb;border-color:#fde68a;margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#7a4a0a;margin-bottom:6px">
            <i class="ti ti-clipboard-check" style="font-size:16px"></i>
            Action required — asset acknowledgement pending
        </div>
        <div style="font-size:12.5px;color:#7a4a0a;margin-bottom:14px">
            You have {{ $pendingAssignments->count() }} asset{{ $pendingAssignments->count() > 1 ? 's' : '' }} that require{{ $pendingAssignments->count() > 1 ? '' : 's' }} your confirmation of receipt.
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
    </div>
@endif
