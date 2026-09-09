<x-prototype-layout title="Maintenance schedule">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module</div>
            <div class="pt">Maintenance schedule</div>
        </div>
    </div>

    @if ($schedules->count() === 0)
        <div class="card" style="text-align:center;color:#64748b;padding:24px">
            No recurring maintenance tasks assigned to you.
        </div>
    @else
        @foreach ($schedules as $schedule)
            @php $isDue = $schedule->isDue(); @endphp
            <div class="card" style="margin-bottom:14px;border-left:3px solid {{ $isDue ? '#f59e0b' : '#4a9eff' }}">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                    <div>
                        <div style="font-weight:700;font-size:14.5px;color:#0f2d5e">{{ $schedule->title }}</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">{{ $schedule->asset->asset_tag ?? '—' }} — {{ $schedule->asset->name ?? '' }}</div>
                    </div>
                    @if ($isDue)
                        <span class="badge b-pend">Due — work order generates soon</span>
                    @else
                        <span class="badge b-prog">{{ ucfirst(str_replace('_', '-', $schedule->frequency)) }}</span>
                    @endif
                </div>

                <div style="font-size:12px;color:#64748b">
                    <i class="ti ti-calendar" style="font-size:12px"></i> Next occurrence: {{ $schedule->next_due_date->format('M j, Y') }}
                    @if ($schedule->estimated_duration_hrs)
                        · Est. {{ $schedule->estimated_duration_hrs }}h
                    @endif
                </div>

                @if ($schedule->description)
                    <div style="background:#f8fafc;border-radius:6px;padding:10px 12px;font-size:12.5px;color:#334155;margin-top:10px">
                        {{ $schedule->description }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif

</x-prototype-layout>
