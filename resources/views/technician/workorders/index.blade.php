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
            <div class="card" style="margin-bottom:14px;border-left:3px solid {{ $wo->isOverdue() ? '#e24b4a' : '#4a9eff' }}">
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
                    <span><i class="ti ti-calendar" style="font-size:12px"></i> Due {{ $wo->due_date?->format('M j, Y g:i A') ?? '—' }}</span>
                    <span><i class="ti ti-tool" style="font-size:12px"></i> {{ ucfirst($wo->maintenance_type) }}</span>
                </div>

                <a href="{{ route('technician.workorders.show', $wo) }}" class="btn sm">
                    <i class="ti ti-eye" style="font-size:13px"></i> View details
                </a>
            </div>
        @endforeach
    @endif

</x-prototype-layout>
