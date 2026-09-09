<x-prototype-layout title="Asset transfers">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Lifecycle</div>
            <div class="pt">Transfers</div>
        </div>
        <a href="{{ route('admin.transfers.create') }}" class="btn pri sm">
            <i class="ti ti-plus" style="font-size:13px"></i> New transfer
        </a>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    <div class="tabs" id="transfer-tabs">
        <div class="tab active" data-group="pending" onclick="showTransferGroup('pending')">Pending ({{ $transfers->where('status', 'pending')->count() }})</div>
        <div class="tab" data-group="approved" onclick="showTransferGroup('approved')">History ({{ $transfers->where('status', 'approved')->count() }})</div>
        <div class="tab" data-group="rejected" onclick="showTransferGroup('rejected')">Rejected ({{ $transfers->where('status', 'rejected')->count() }})</div>
    </div>

    <div id="transfer-group-pending">
        @forelse ($transfers->where('status', 'pending') as $t)
            <div class="card" style="margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                    <div>
                        <div style="font-size:11px;color:#94a3b8">Requested {{ $t->requested_at->format('M j, Y') }} by {{ $t->requestedBy->first_name ?? '' }} {{ $t->requestedBy->last_name ?? '' }}</div>
                        <div style="font-weight:700;font-size:14.5px;color:#0f2d5e;margin-top:2px">{{ $t->asset->name ?? '—' }} ({{ $t->asset->asset_tag ?? '' }})</div>
                    </div>
                    <span class="badge b-pend">Pending</span>
                </div>

                <div style="font-size:12.5px;color:#334155;margin-bottom:8px">
                    <i class="ti ti-arrow-right" style="font-size:12px"></i>
                    {{ $t->fromHolder->first_name ?? 'Unassigned' }} {{ $t->fromHolder->last_name ?? '' }}
                    →
                    {{ $t->toHolder->first_name ?? '' }} {{ $t->toHolder->last_name ?? '' }}
                    @if ($t->toLocation)
                        · New location: {{ $t->toLocation->name }}
                    @endif
                </div>

                <div style="background:#f8fafc;border-radius:6px;padding:10px 12px;font-size:12.5px;color:#334155;margin-bottom:12px">
                    {{ $t->reason }}
                </div>

                <div class="fa">
                    <form method="POST" action="{{ route('admin.transfers.approve', $t) }}" style="display:inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn pri sm">
                            <i class="ti ti-check" style="font-size:13px"></i> Approve
                        </button>
                    </form>
                    <button class="btn sm" style="border-color:#e24b4a;color:#e24b4a" onclick="document.getElementById('modal-reject-{{ $t->id }}').style.display='flex'">
                        <i class="ti ti-x" style="font-size:13px"></i> Reject
                    </button>
                </div>
            </div>

            <div id="modal-reject-{{ $t->id }}" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-hdr">
                        <div class="modal-ttl">Reject transfer</div>
                        <button class="modal-close" onclick="document.getElementById('modal-reject-{{ $t->id }}').style.display='none'">&times;</button>
                    </div>
                    <form method="POST" action="{{ route('admin.transfers.reject', $t) }}">
                        @csrf
                        @method('PATCH')
                        <div class="fg">
                            <label>Reason (optional)</label>
                            <textarea name="rejection_reason" placeholder="Why is this being rejected?"></textarea>
                        </div>
                        <div class="fa">
                            <button type="submit" class="btn sm" style="background:#e24b4a;color:#fff;border-color:#e24b4a">Confirm rejection</button>
                            <button type="button" class="btn sm" onclick="document.getElementById('modal-reject-{{ $t->id }}').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No pending transfer requests.</div>
        @endforelse
    </div>

    <div id="transfer-group-approved" style="display:none">
        @forelse ($transfers->where('status', 'approved') as $t)
            <div class="card" style="margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <div style="font-weight:700;font-size:13.5px;color:#0f2d5e">{{ $t->asset->name ?? '—' }} ({{ $t->asset->asset_tag ?? '' }})</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">
                            {{ $t->fromHolder->first_name ?? 'Unassigned' }} → {{ $t->toHolder->first_name ?? '' }} {{ $t->toHolder->last_name ?? '' }}
                            · Approved {{ $t->approved_at->format('M j, Y') }}
                        </div>
                    </div>
                    <span class="badge b-act">Completed</span>
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No completed transfers yet.</div>
        @endforelse
    </div>

    <div id="transfer-group-rejected" style="display:none">
        @forelse ($transfers->where('status', 'rejected') as $t)
            <div class="card" style="margin-bottom:14px">
                <div style="font-weight:700;font-size:13.5px;color:#0f2d5e">{{ $t->asset->name ?? '—' }} ({{ $t->asset->asset_tag ?? '' }})</div>
                <div style="font-size:12px;color:#64748b;margin:4px 0">{{ $t->fromHolder->first_name ?? '' }} → {{ $t->toHolder->first_name ?? '' }} · Rejected {{ $t->approved_at->format('M j, Y') }}</div>
                @if ($t->rejection_reason)
                    <div style="background:#fff0f0;border-radius:6px;padding:8px 11px;font-size:12px;color:#a32d2d;margin-top:6px">{{ $t->rejection_reason }}</div>
                @endif
            </div>
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No rejected transfers.</div>
        @endforelse
    </div>

    <script>
        function showTransferGroup(group) {
            document.querySelectorAll('#transfer-tabs .tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`#transfer-tabs [data-group="${group}"]`).classList.add('active');
            document.getElementById('transfer-group-pending').style.display = group === 'pending' ? '' : 'none';
            document.getElementById('transfer-group-approved').style.display = group === 'approved' ? '' : 'none';
            document.getElementById('transfer-group-rejected').style.display = group === 'rejected' ? '' : 'none';
        }
    </script>

</x-prototype-layout>
