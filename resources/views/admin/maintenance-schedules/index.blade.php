<x-prototype-layout title="Maintenance schedule">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">Maintenance schedule</div>
        </div>
        <a href="{{ route('admin.maintenance-schedules.create') }}" class="btn pri sm">
            <i class="ti ti-plus" style="font-size:13px"></i> New schedule
        </a>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th style="width:150px">Asset</th>
                    <th style="width:150px">Title</th>
                    <th style="width:100px">Frequency</th>
                    <th style="width:110px">Next due</th>
                    <th style="width:130px">Technician</th>
                    <th style="width:80px">Status</th>
                    <th style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schedules as $schedule)
                    @php $isDue = $schedule->isDue(); @endphp
                    <tr>
                        <td>{{ $schedule->asset->asset_tag ?? '—' }}</td>
                        <td>{{ $schedule->title }}</td>
                        <td>{{ ucfirst(str_replace('_', '-', $schedule->frequency)) }}</td>
                        <td>
                            {{ $schedule->next_due_date->format('M j, Y') }}
                            @if ($isDue)
                                <span class="badge b-pend" style="font-size:10px;margin-left:4px">Due</span>
                            @endif
                        </td>
                        <td>{{ $schedule->defaultAssignee->first_name ?? '—' }} {{ $schedule->defaultAssignee->last_name ?? '' }}</td>
                        <td>
                            @if ($schedule->is_active)
                                <span class="badge b-act">Active</span>
                            @else
                                <span class="badge b-canc">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="ab">
                                <a href="{{ route('admin.maintenance-schedules.edit', $schedule) }}" class="ac v">Edit</a>
                                <form method="POST" action="{{ route('admin.maintenance-schedules.toggle', $schedule) }}" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ac {{ $schedule->is_active ? 'd' : 'e' }}">
                                        {{ $schedule->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:#64748b;padding:24px">
                            No recurring maintenance schedules yet. Click "New schedule" to set up your first one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-prototype-layout>
