<x-prototype-layout title="Technician">
<div class="ph">
<div class="ph-l">
<div class="bc">Technician</div>
<div class="pt">Dashboard overview</div>
</div>
</div>
@if (session('status'))
<div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">{{ session('status') }}</div>
@endif
@include('partials.pending-assignments-banner')
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px">
<div class="card">
<div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-tool" style="font-size:13px"></i> Open work orders</div>
<div style="font-size:26px;font-weight:700;color:#0f2d5e">{{ $openCount }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px">{{ $inProgress }} in progress</div>
</div>
<div class="card">
<div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-calendar-due" style="font-size:13px"></i> Due today</div>
<div style="font-size:26px;font-weight:700;color:#7a4a0a">{{ $dueToday }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px">Finish before end of day</div>
</div>
<div class="card">
<div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-alert-triangle" style="font-size:13px"></i> Overdue</div>
<div style="font-size:26px;font-weight:700;color:{{ $overdue > 0 ? '#b91c1c' : '#2d7d32' }}">{{ $overdue }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px">Past due date</div>
</div>
<div class="card">
<div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-circle-check" style="font-size:13px"></i> Completed this month</div>
<div style="font-size:26px;font-weight:700;color:#2d7d32">{{ $completedMonth }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px">{{ now()->format('F Y') }}</div>
</div>
</div>
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:18px">
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e">Next jobs</div>
<a href="{{ url('/technician/work-orders') }}" style="font-size:12px;color:#185fa5">View all</a>
</div>
@forelse ($nextJobs as $wo)
<a href="{{ url('/technician/work-orders/'.$wo->id) }}" style="display:flex;justify-content:space-between;align-items:center;gap:10px;padding:9px 0;border-top:0.5px solid #e5e9f0;text-decoration:none">
<div>
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $wo->asset->name ?? 'Asset' }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $wo->work_order_number ?? ('#'.$wo->id) }} · {{ ucfirst($wo->priority ?? 'medium') }} priority · Due {{ $wo->due_date ? \Carbon\Carbon::parse($wo->due_date)->format('M j') : 'not set' }}</div>
</div>
<div style="text-align:right;flex-shrink:0">
<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#f1f5f9;color:#334155">{{ ucfirst(str_replace('_',' ',$wo->status)) }}</span>
@if ($wo->sla_breached)
<div style="font-size:11px;color:#b91c1c;margin-top:3px">SLA breached</div>
@elseif ($wo->due_date && \Carbon\Carbon::parse($wo->due_date)->isPast() && !\Carbon\Carbon::parse($wo->due_date)->isToday())
<div style="font-size:11px;color:#b91c1c;margin-top:3px">Overdue</div>
@endif
</div>
</a>
@empty
<div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No open work orders.</div>
@endforelse
</div>
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e">Upcoming maintenance</div>
<a href="{{ url('/technician/maintenance-schedule') }}" style="font-size:12px;color:#185fa5">View all</a>
</div>
@forelse ($upcoming as $s)
<div style="padding:9px 0;border-top:0.5px solid #e5e9f0">
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $s->title }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $s->asset->name ?? 'Asset' }} · {{ ucfirst(str_replace('_',' ',$s->frequency)) }}</div>
<div style="font-size:11px;margin-top:2px;color:{{ $s->next_due_date->isPast() ? '#b91c1c' : '#94a3b8' }}">Next due {{ $s->next_due_date->format('M j, Y') }}</div>
</div>
@empty
<div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No scheduled maintenance assigned to you.</div>
@endforelse
</div>
</div>
</x-prototype-layout>
