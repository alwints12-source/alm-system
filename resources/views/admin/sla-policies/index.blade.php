<x-prototype-layout title="SLA policies">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">SLA & compliance</div>
        </div>
        <a href="{{ route('admin.sla-policies.create') }}" class="btn pri sm">
            <i class="ti ti-plus" style="font-size:13px"></i> New policy
        </a>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12.5px;color:#7a4a0a">
        <i class="ti ti-info-circle" style="font-size:14px"></i>
        Live SLA countdowns and breach tracking on individual work orders are coming in the next update. This page manages the policies themselves — the actual targets each request gets measured against.
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th style="width:160px">Policy name</th>
                    <th style="width:100px">Priority</th>
                    <th style="width:110px">Type</th>
                    <th style="width:130px">Category</th>
                    <th style="width:90px">Response</th>
                    <th style="width:90px">Resolution</th>
                    <th style="width:80px">Status</th>
                    <th style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($policies as $policy)
                    @php
                        $priorityClass = match($policy->priority) {
                            'critical' => 'b-crit', 'high' => 'b-hi', 'medium' => 'b-med', 'low' => 'b-lo', default => '',
                        };
                    @endphp
                    <tr>
                        <td>{{ $policy->name }}</td>
                        <td><span class="badge {{ $priorityClass }}">{{ ucfirst($policy->priority) }}</span></td>
                        <td>{{ $policy->maintenance_type ? ucfirst($policy->maintenance_type) : 'Any' }}</td>
                        <td>{{ $policy->category->name ?? 'Any' }}</td>
                        <td>{{ $policy->response_time_hours }}h</td>
                        <td>{{ $policy->resolution_time_hours }}h</td>
                        <td>
                            @if ($policy->is_active)
                                <span class="badge b-act">Active</span>
                            @else
                                <span class="badge b-canc">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="ab">
                                <a href="{{ route('admin.sla-policies.edit', $policy) }}" class="ac v">Edit</a>
                                <form method="POST" action="{{ route('admin.sla-policies.toggle', $policy) }}" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ac {{ $policy->is_active ? 'd' : 'e' }}">
                                        {{ $policy->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;color:#64748b;padding:24px">
                            No SLA policies yet. Click "New policy" to define your first response/resolution target.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-prototype-layout>
