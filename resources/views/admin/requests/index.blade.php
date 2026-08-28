<x-prototype-layout title="Maintenance requests">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">Maintenance requests</div>
        </div>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    <div class="tabs" id="request-tabs">
        <div class="tab active" data-group="incoming" onclick="showGroup('incoming')">Incoming ({{ $requests->where('status', 'pending')->count() }})</div>
        <div class="tab" data-group="approved" onclick="showGroup('approved')">Approved ({{ $requests->whereIn('status', ['assigned', 'in_progress', 'completed'])->count() }})</div>
        <div class="tab" data-group="rejected" onclick="showGroup('rejected')">Rejected ({{ $requests->where('status', 'cancelled')->count() }})</div>
    </div>

    <div id="group-incoming">
        @forelse ($requests->where('status', 'pending') as $wo)
            @include('admin.requests._card', ['wo' => $wo, 'showActions' => true])
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No incoming requests.</div>
        @endforelse
    </div>

    <div id="group-approved" style="display:none">
        @forelse ($requests->whereIn('status', ['assigned', 'in_progress', 'completed']) as $wo)
            @include('admin.requests._card', ['wo' => $wo, 'showActions' => false])
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No approved requests yet.</div>
        @endforelse
    </div>

    <div id="group-rejected" style="display:none">
        @forelse ($requests->where('status', 'cancelled') as $wo)
            @include('admin.requests._card', ['wo' => $wo, 'showActions' => false])
        @empty
            <div class="card" style="text-align:center;color:#64748b;padding:24px">No rejected requests.</div>
        @endforelse
    </div>

    {{-- Approve & Assign modals — one per pending request --}}
    @foreach ($requests->where('status', 'pending') as $wo)
        <div id="modal-approve-{{ $wo->id }}" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-hdr">
                    <div class="modal-ttl">Approve & assign request</div>
                    <button class="modal-close" onclick="document.getElementById('modal-approve-{{ $wo->id }}').style.display='none'">&times;</button>
                </div>

                <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:14px;font-size:12.5px">
                    <strong>{{ $wo->work_order_number }} — {{ $wo->title }}</strong><br>
                    Submitted by {{ $wo->requestedBy->first_name ?? '—' }} {{ $wo->requestedBy->last_name ?? '' }} · {{ $wo->asset->asset_tag ?? '' }}
                </div>

                <form method="POST" action="{{ route('admin.requests.approve', $wo) }}">
                    @csrf
                    @method('PATCH')

                    <div class="fg">
                        <label>Assign technician *</label>
                        <select name="assigned_to" required>
                            <option value="">Select technician</option>
                            @foreach ($technicians as $tech)
                                <option value="{{ $tech->id }}">
                                    {{ $tech->first_name }} {{ $tech->last_name }} — {{ $tech->open_jobs }} open {{ Str::plural('job', $tech->open_jobs) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fg2">
                        <div class="fg">
                            <label>Scheduled date *</label>
                            <input type="date" name="due_date" required>
                        </div>
                        <div class="fg">
                            <label>Scheduled time *</label>
                            <input type="time" name="due_time" required>
                        </div>
                    </div>

                    <div class="fg2">
                        <div class="fg">
                            <label>Maintenance type *</label>
                            <select name="maintenance_type" required>
                                <option value="corrective">Corrective — fix a fault</option>
                                <option value="preventive">Preventive</option>
                                <option value="predictive">Predictive</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label>Estimated cost (₱)</label>
                            <input type="number" step="0.01" name="estimated_cost" placeholder="0.00">
                        </div>
                    </div>

                    <div class="fg">
                        <label>Notes to technician</label>
                        <textarea name="technician_notes" placeholder="Any additional instructions or context for the assigned technician..."></textarea>
                    </div>

                    <div style="background:#f0fdf4;border-radius:6px;padding:10px 12px;margin-bottom:14px;font-size:11.5px;color:#2d7d32">
                        On approval, a standard 5-step checklist is created automatically and the asset holder is notified with the scheduled date and assigned technician.
                    </div>

                    <div class="fa">
                        <button type="submit" class="btn pri sm">Confirm approval & create WO</button>
                        <button type="button" class="btn sm" onclick="document.getElementById('modal-approve-{{ $wo->id }}').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modal-reject-{{ $wo->id }}" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-hdr">
                    <div class="modal-ttl">Reject request</div>
                    <button class="modal-close" onclick="document.getElementById('modal-reject-{{ $wo->id }}').style.display='none'">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.requests.reject', $wo) }}">
                    @csrf
                    @method('PATCH')

                    <div class="fg">
                        <label>Reason (optional)</label>
                        <textarea name="resolution_notes" placeholder="Why is this being rejected?"></textarea>
                    </div>

                    <div class="fa">
                        <button type="submit" class="btn sm" style="background:#e24b4a;color:#fff;border-color:#e24b4a">Confirm rejection</button>
                        <button type="button" class="btn sm" onclick="document.getElementById('modal-reject-{{ $wo->id }}').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        function showGroup(group) {
            document.querySelectorAll('#request-tabs .tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`#request-tabs [data-group="${group}"]`).classList.add('active');
            document.getElementById('group-incoming').style.display = group === 'incoming' ? '' : 'none';
            document.getElementById('group-approved').style.display = group === 'approved' ? '' : 'none';
            document.getElementById('group-rejected').style.display = group === 'rejected' ? '' : 'none';
        }
    </script>

</x-prototype-layout>
