<x-prototype-layout title="Roles and permissions">
<div class="ph">
<div class="ph-l">
<div class="bc">Technical Admin</div>
<div class="pt">Role and permission management</div>
</div>
</div>
<div style="font-size:12.5px;color:#64748b;margin-bottom:14px">The system has four fixed roles. Access is enforced on the server by role, so a user can only open pages that belong to their role.</div>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px">
@foreach ($roles as $key => $r)
@php
$c = $counts[$key] ?? null;
$granted = collect($permissions)->filter(fn ($p) => in_array($key, $p[1]))->count();
@endphp
<div class="card" style="border-left:4px solid {{ $r['color'] }}">
<div style="font-size:14px;font-weight:700;color:#0f2d5e">{{ $r['label'] }}</div>
<div style="font-size:12px;color:#64748b;margin:3px 0 10px">{{ $r['desc'] }}</div>
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $c->total ?? 0 }} users assigned</div>
<div style="font-size:11.5px;color:#94a3b8">{{ $c->active ?? 0 }} active</div>
<div style="font-size:11.5px;color:#94a3b8;margin-bottom:10px">{{ $granted }} permissions granted</div>
<a href="{{ url('/techadmin/users?role='.$key) }}" class="btn sm">View users</a>
</div>
@endforeach
</div>
<div class="card">
<div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:12px">Role permission matrix</div>
<div class="tw">
<table>
<thead>
<tr>
<th>Permission</th>
@foreach ($roles as $r)
<th style="text-align:center;width:70px">{{ $r['abbr'] }}</th>
@endforeach
</tr>
</thead>
<tbody>
@foreach ($permissions as $p)
<tr>
<td>{{ $p[0] }}</td>
@foreach ($roles as $key => $r)
<td style="text-align:center">{!! in_array($key, $p[1]) ? '<span style="color:#2d7d32;font-weight:700">&#10003;</span>' : '<span style="color:#cbd5e1">&mdash;</span>' !!}</td>
@endforeach
</tr>
@endforeach
</tbody>
</table>
</div>
<div style="font-size:11.5px;color:#94a3b8;margin-top:10px">AA = Administrative Admin, TA = Technical Admin, AH = Asset Holder, TC = Technician</div>
</div>
</x-prototype-layout>
