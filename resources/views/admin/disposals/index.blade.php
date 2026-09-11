<x-prototype-layout title="Asset disposals">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Lifecycle</div>
            <div class="pt">Disposals</div>
        </div>
        <a href="{{ route('admin.disposals.create') }}" class="btn pri sm">
            <i class="ti ti-plus" style="font-size:13px"></i> New request
        </a>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    <div class="tabs" id="disposal-tabs">
        <div class="tab active" data-group="pending" onclick="showDisposalGroup('pending')">Pending ({{ $disposals->where('status', 'pending')->count() }})</div>
        <div class="tab" data-group="approved" onclick="showDisposalGroup('approved')">Records ({{ $disposals->where('status', 'approved')->count() }})</div>
        <div class="tab" data-group="rejected" onclick="showDisposalGroup('rejected')">Rejected ({{ $disposals->where('status', 'rejected')->count() }})</div>
    </div>

    <div id="disposal-group-pending">
        @forelse ($disposals->where('status', 'pending') as $d)
            <div class="card" style="margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                    <div>
                        <div style="font-size:11px;color:#94a3b8">
                            Requested {{ $d->requested_at->format('M j, Y') }} by {{ $d->requestedBy->first_name ?? '' }} {{ $d->requestedBy->last_name ?? '' }}
                            @if ($d->source_work_order_id)
                                · <i class="ti ti-tool" style="font-size:11px"></i> flagged during a repair
                            @endif
                        </div>
                        <div style="font-weight:700;font-size:14.5px;color:#0f2d5e;margin-top:2px">{{ $d->asset->name ?? '—' }} ({{ $d->asset->asset_tag ?? '' }})</div>
                    </div>
                    <span class="badge b-pend">Pending</span>
                </div>

                <div style="background:#f8fafc;border-radius:6px;padding:10px 12px;font-size:12.5px;color:#334155;margin-bottom:12px">
                    {{ $d->reason }}
                </div>

                <div class="fa">
                    <button class="btn pri sm" onclick="document.getElementById('modal-approve-{{ $d->id }}').style.display='flex'">
                        <i class="ti ti-check" style="font-size:13px"></i> Approve
                    </button>
                    <button class="btn sm" style="border-color:#e24b4a;color:#e24b4a" onclick="document.getElementById('modal-reject-{{ $d->id }}').style.display='flex'">
                        <i class="ti ti-x" style="font-size:13px"></i> Reject
                    </button>
                </div>
            </div>

            <div id="modal-approve-{{ $d->id }}" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-hdr">
                        <div class="modal-ttl">Approve disposal — {{ $d->asset->name ?? '' }}</div>
                        <button class="modal-close" onclick="document.getElementById('modal-approve-{{ $d->id }}').style.display='none'">&times;</button>
                    </div>
                    <form method="POST" action="{{ route('admin.disposals.approve', $d) }}">
                        @csrf
                        @method('PATCH')
                        <div class="fg">
                            <label>Disposal method *</label>
                            <select name="disposal_method" required>
                                <option value="">Select method</option>
                                <option value="scrapped">Scrapped</option>
                                <option value="donated">Donated</option>
                                <option value="sold">Sold</option>
                                <option value="recycled">Recycled</option>
                            </select>
                        </div>
                        <div style="background:#fff0f0;border-radius:6px;padding:8px 11px;margin-bottom:14px;font-size:12px;color:#a32d2d">
                            This permanently marks the asset as disposed and ends any active assignment.
                        </div>
                        <div class="fa">
                            <button type="submit" class="btn pri sm">Confirm disposal</button>
                            <button type="button" class="btn sm" onclick="document.getElementById('modal-approve-{{ $d->id }}').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="modal-reject-{{ $d->id }}" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-hdr">
                        <div class="modal-ttl">Reject disposal request</div>
                        <button class="modal-close" onclick="document.getElementById('modal-reject-{{ $d->id }}').style.display='none'">&times;</button>
                    </div>
                    <form method="POST" action="{{ route('admin.disposals.reject', $d) }}">
                        @csrf
                        @method('PATCH')
                        <div class="fg">
                            <label>Reason (optional)</label>
                            <textarea name="rejection_reason" placeholder="Why keep this asset in service?"></textarea>
                        </div>
                        <div class="fa">
                            <button type="submit" class="btn sm" style="background:#e24b4a;color:#fff;border-color:#e24b4a">Confirm rejection</button>
                            <button type="button" class="btn sm" onclick="document.getElementById('modal-reject-{{ $d->id }}').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No pending disposal requests.</div>
        @endforelse
    </div>

    <div id="disposal-group-approved" style="display:none">
        @forelse ($disposals->where('status', 'approved') as $d)
            <div class="card" style="margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <div style="font-weight:700;font-size:13.5px;color:#0f2d5e">{{ $d->asset->name ?? '—' }} ({{ $d->asset->asset_tag ?? '' }})</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">{{ ucfirst($d->disposal_method) }} · {{ $d->approved_at->format('M j, Y') }}</div>
                    </div>
                    <span class="badge b-canc">Disposed</span>
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No disposal records yet.</div>
        @endforelse
    </div>

    <div id="disposal-group-rejected" style="display:none">
        @forelse ($disposals->where('status', 'rejected') as $d)
            <div class="card" style="margin-bottom:14px">
                <div style="font-weight:700;font-size:13.5px;color:#0f2d5e">{{ $d->asset->name ?? '—' }} ({{ $d->asset->asset_tag ?? '' }})</div>
                <div style="font-size:12px;color:#64748b;margin:4px 0">Rejected {{ $d->approved_at->format('M j, Y') }}</div>
                @if ($d->rejection_reason)
                    <div style="background:#fff0f0;border-radius:6px;padding:8px 11px;font-size:12px;color:#a32d2d;margin-top:6px">{{ $d->rejection_reason }}</div>
                @endif
            </div>
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No rejected requests.</div>
        @endforelse
    </div>

    <script>
        function showDisposalGroup(group) {
            document.querySelectorAll('#disposal-tabs .tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`#disposal-tabs [data-group="${group}"]`).classList.add('active');
            document.getElementById('disposal-group-pending').style.display = group === 'pending' ? '' : 'none';
            document.getElementById('disposal-group-approved').style.display = group === 'approved' ? '' : 'none';
            document.getElementById('disposal-group-rejected').style.display = group === 'rejected' ? '' : 'none';
        }
    </script>

</x-prototype-layout>
