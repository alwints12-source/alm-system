<x-prototype-layout title="Work orders">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">Work orders</div>
        </div>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    @php
        $cnt = [
            'all' => $workOrders->count(),
            'assigned' => $workOrders->where('status', 'assigned')->count(),
            'in_progress' => $workOrders->where('status', 'in_progress')->count(),
            'completed' => $workOrders->where('status', 'completed')->count(),
            'overdue' => $workOrders->filter(fn ($w) => $w->isOverdue())->count(),
        ];
    @endphp
    <div style="display:flex;gap:22px;border-bottom:1px solid #e5e9f0;margin-bottom:14px;flex-wrap:wrap">
        @foreach (['all' => 'All', 'assigned' => 'Assigned', 'in_progress' => 'In progress', 'completed' => 'Completed', 'overdue' => 'Overdue'] as $k => $label)
            <button type="button" class="wo-tab" data-tab="{{ $k }}" style="background:none;border:none;border-bottom:2px solid transparent;padding:8px 2px;font-size:13px;font-weight:600;cursor:pointer;color:#94a3b8">{{ $label }} ({{ $cnt[$k] }})</button>
        @endforeach
    </div>
    <div style="display:flex;gap:10px;margin-bottom:14px">
        <input id="wo-search" type="text" placeholder="Search work orders..." style="flex:1;padding:9px 12px;border:1px solid #e5e9f0;border-radius:8px;font-size:13px">
        <select id="wo-priority" style="padding:9px 12px;border:1px solid #e5e9f0;border-radius:8px;font-size:13px">
            <option value="">All priority</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
    </div>
    <div id="wo-empty" class="card" style="display:none;text-align:center;color:#94a3b8;padding:24px">No work orders match.</div>

    @if ($workOrders->count() === 0)
        <div class="card" style="text-align:center;color:#64748b;padding:24px">
            No work orders assigned to you yet.
        </div>
    @else
        @foreach ($workOrders as $wo)
            @php
                $priorityClass = match($wo->priority) {
                    'critical' => 'b-crit', 'high' => 'b-hi', 'medium' => 'b-med', 'low' => 'b-lo', default => '',
                };
                $statusClass = match($wo->status) {
                    'assigned' => 'b-pend', 'in_progress' => 'b-prog', 'completed' => 'b-done', default => '',
                };
                $statusLabel = match($wo->status) {
                    'assigned' => 'Assigned', 'in_progress' => 'In progress', 'completed' => 'Completed', default => ucfirst($wo->status),
                };

                $slaBadge = null;
                if (in_array($wo->status, ['assigned', 'in_progress'])) {
                    if ($wo->sla_breached) {
                        $slaBadge = ['text' => 'SLA breached', 'color' => '#e24b4a'];
                    } else {
                        $dueAt = $wo->resolutionDueAt();
                        if ($dueAt) {
                            if ($dueAt->isPast()) {
                                $slaBadge = ['text' => 'Resolution overdue', 'color' => '#e24b4a'];
                            } else {
                                $seconds = now()->diffInSeconds($dueAt);
                                $hours = intdiv($seconds, 3600);
                                $minutes = intdiv($seconds % 3600, 60);
                                $precise = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
                                $slaBadge = ['text' => "Due in {$precise}", 'color' => '#185fa5'];
                            }
                        }
                    }
                }
            @endphp
            <div class="card wo-card" data-tabs="all {{ $wo->status }}{{ $wo->isOverdue() ? ' overdue' : '' }}" data-priority="{{ $wo->priority }}" data-text="{{ strtolower($wo->work_order_number.' '.$wo->title.' '.($wo->asset->asset_tag ?? '').' '.($wo->asset->name ?? '')) }}" style="margin-bottom:14px;border-left:3px solid {{ ['critical'=>'#e24b4a','high'=>'#f08a24','medium'=>'#f2b01e','low'=>'#4caf50'][$wo->priority] ?? '#4a9eff' }}">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                    <div>
                        <div style="font-size:11px;color:#94a3b8">{{ $wo->work_order_number }}</div>
                        <div style="font-weight:700;font-size:14.5px;color:#0f2d5e;margin-top:2px">{{ $wo->title }}</div>
                    </div>
                    <div style="display:flex;gap:5px;align-items:center">
                        @if ($slaBadge)
                            <span style="font-size:10.5px;font-weight:600;color:{{ $slaBadge['color'] }}">{{ $slaBadge['text'] }}</span>
                        @endif
                        <span class="badge {{ $priorityClass }}">{{ ucfirst($wo->priority) }}</span>
                        @if ($wo->isOverdue())
                            <span class="badge b-over">Overdue</span>
                        @else
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        @endif
                    </div>
                </div>

                <div style="font-size:12px;color:#64748b;margin-bottom:10px;display:flex;gap:12px;flex-wrap:wrap">
                    <span><i class="ti ti-package" style="font-size:12px"></i> {{ $wo->asset->asset_tag ?? '—' }}</span>
                    <span><i class="ti ti-user" style="font-size:12px"></i> {{ $wo->requestedBy->name ?? '—' }}</span>
                    <span><i class="ti ti-calendar" style="font-size:12px"></i> Due {{ $wo->due_date?->format('M j, Y g:i A') ?? '—' }}</span>
                    <span><i class="ti ti-tool" style="font-size:12px"></i> {{ ucfirst($wo->maintenance_type) }}</span>
                </div>

                <a href="{{ route('technician.workorders.show', $wo) }}" class="btn sm">
                    <i class="ti ti-eye" style="font-size:13px"></i> View details
                </a>
            </div>
        @endforeach
    @endif

<script>
(function () {
    var tab = 'all', tabs = document.querySelectorAll('.wo-tab'), cards = document.querySelectorAll('.wo-card');
    var q = document.getElementById('wo-search'), p = document.getElementById('wo-priority'), empty = document.getElementById('wo-empty');
    if (!cards.length) { return; }
    function paint() {
        tabs.forEach(function (t) {
            var on = t.dataset.tab === tab;
            t.style.color = on ? '#0f2d5e' : '#94a3b8';
            t.style.borderBottomColor = on ? '#0f2d5e' : 'transparent';
        });
    }
    function apply() {
        var n = 0, s = q.value.toLowerCase();
        cards.forEach(function (c) {
            var ok = c.dataset.tabs.split(' ').indexOf(tab) > -1 && (!p.value || c.dataset.priority === p.value) && c.dataset.text.indexOf(s) > -1;
            c.style.display = ok ? '' : 'none';
            if (ok) { n++; }
        });
        empty.style.display = n ? 'none' : '';
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { tab = t.dataset.tab; paint(); apply(); }); });
    q.addEventListener('input', apply);
    p.addEventListener('change', apply);
    paint(); apply();
})();
</script>
</x-prototype-layout>
