<x-prototype-layout title="My assets">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Holder</div>
            <div class="pt">My assets</div>
        </div>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

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
                        <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Condition</div>
                        <div style="font-size:13px;text-transform:capitalize">{{ $asset->condition }}</div>
                    </div>
                </div>

                <div style="margin-top:14px;display:flex;gap:8px">
                    <button class="btn sm" disabled title="Coming in a later sprint">View full details</button>
                    <button class="btn pri sm" onclick="document.getElementById('modal-report-{{ $assignment->id }}').style.display='flex'">
                        <i class="ti ti-alert-triangle" style="font-size:13px"></i> Report issue
                    </button>
                </div>
            </div>

            {{-- Report Issue modal for this asset --}}
            <div id="modal-report-{{ $assignment->id }}" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-hdr">
                        <div class="modal-ttl">Report issue — {{ $asset->name }}</div>
                        <button class="modal-close" onclick="document.getElementById('modal-report-{{ $assignment->id }}').style.display='none'">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('holder.assets.reportIssue', $assignment) }}">
                        @csrf

                        <div class="fg">
                            <label>Issue summary *</label>
                            <input type="text" name="title" placeholder="e.g. Paper jam frequently occurring" required>
                        </div>

                        <div class="fg">
                            <label>Description *</label>
                            <textarea name="description" placeholder="Describe the problem in detail..." required></textarea>
                        </div>

                        <div class="fg">
                            <label>Priority *</label>
                            <select name="priority" required>
                                <option value="">Select priority</option>
                                <option value="critical">Critical — asset unusable / safety risk</option>
                                <option value="high">High — significantly affecting work</option>
                                <option value="medium">Medium — noticeable but workable</option>
                                <option value="low">Low — minor / cosmetic</option>
                            </select>
                        </div>

                        <div class="fa">
                            <button type="submit" class="btn pri sm">Submit report</button>
                            <button type="button" class="btn sm" onclick="document.getElementById('modal-report-{{ $assignment->id }}').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

</x-prototype-layout>
