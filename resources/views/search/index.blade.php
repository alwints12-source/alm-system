<x-prototype-layout title="Search">
<div class="ph">
<div class="ph-l">
<div class="bc">Search</div>
<div class="pt">{{ $q === '' ? 'Search' : 'Results for “'.$q.'”' }}</div>
</div>
</div>
@php $total = $assets->count() + $workOrders->count() + $users->count() + $assignments->count(); @endphp
@if ($q === '')
<div class="card" style="text-align:center;color:#94a3b8;padding:24px">Type something in the search box and press Enter.</div>
@elseif ($total === 0)
<div class="card" style="text-align:center;color:#94a3b8;padding:24px">No results found.</div>
@endif
@if ($assets->count())
<div class="card" style="margin-bottom:14px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:8px">Assets</div>
@foreach ($assets as $asset)
<a href="{{ route('admin.assets.show', $asset) }}" style="display:block;padding:9px 0;border-top:0.5px solid #e5e9f0;text-decoration:none">
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $asset->name }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $asset->asset_tag }}</div>
</a>
@endforeach
</div>
@endif
@if ($assignments->count())
<div class="card" style="margin-bottom:14px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:8px">My assets</div>
@foreach ($assignments as $a)
<a href="{{ route('holder.assets.show', $a) }}" style="display:block;padding:9px 0;border-top:0.5px solid #e5e9f0;text-decoration:none">
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $a->asset->name }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $a->asset->asset_tag }}</div>
</a>
@endforeach
</div>
@endif
@if ($workOrders->count())
<div class="card" style="margin-bottom:14px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:8px">Work orders</div>
@foreach ($workOrders as $wo)
@php
$url = auth()->user()->role === 'administrative_admin' ? url('/admin/requests') : ($wo->assigned_to === auth()->id() ? route('technician.workorders.show', $wo) : url('/holder/requests'));
@endphp
<a href="{{ $url }}" style="display:block;padding:9px 0;border-top:0.5px solid #e5e9f0;text-decoration:none">
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $wo->title }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $wo->work_order_number }} · {{ $wo->asset->name ?? 'Asset' }} · {{ ucfirst(str_replace('_', ' ', $wo->status)) }}</div>
</a>
@endforeach
</div>
@endif
@if ($users->count())
<div class="card" style="margin-bottom:14px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:8px">Users</div>
@foreach ($users as $u)
<a href="{{ url('/techadmin/users') }}" style="display:block;padding:9px 0;border-top:0.5px solid #e5e9f0;text-decoration:none">
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $u->first_name }} {{ $u->last_name }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $u->email }} · {{ \Illuminate\Support\Str::headline($u->role) }}</div>
</a>
@endforeach
</div>
@endif
</x-prototype-layout>
