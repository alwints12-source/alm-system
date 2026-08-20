<x-prototype-layout title="Work order detail">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Maintenance module › Work orders</div>
            <div class="pt">{{ $workOrder->work_order_number }} — {{ $workOrder->title }}</div>
        </div>
        <a href="{{ route('technician.workorders.index') }}" class="btn sm">
            <i class="ti ti-arrow-left" style="font-size:13px"></i> Back
        </a>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card" style="margin-bottom:16px">
        <div style="font-size:13px;font-weight:600;color:#0f2d5e;margin-bottom:12px">Work order information</div>

        <div class="fg2">
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Asset</div>
                <div style="font-size:13px">{{ $workOrder->asset->asset_tag }} — {{ $workOrder->asset->name }}</div>
            </div>
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Reported by</div>
                <div style="font-size:13px">{{ $workOrder->requestedBy->first_name ?? '—' }} {{ $workOrder->requestedBy->last_name ?? '' }}</div>
            </div>
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Due date</div>
                <div style="font-size:13px">{{ $workOrder->due_date?->format('M j, Y g:i A') ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Priority</div>
                <div style="font-size:13px;text-transform:capitalize">{{ $workOrder->priority }}</div>
            </div>
        </div>

        <div class="fg" style="margin-top:14px">
            <label>Description</label>
            <div style="background:#f8fafc;border-radius:8px;padding:12px;font-size:13px;color:#334155">
                {{ $workOrder->description }}
            </div>
        </div>
    </div>

    @if ($workOrder->status === 'assigned')
        <div class="card">
            <div style="font-size:13px;font-weight:600;color:#0f2d5e;margin-bottom:10px">Ready to begin</div>
            <form method="POST" action="{{ route('technician.workorders.start', $workOrder) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn pri sm">
                    <i class="ti ti-player-play" style="font-size:13px"></i> Start work
                </button>
            </form>
        </div>
    @elseif ($workOrder->status === 'in_progress')
        <div class="card">
            <div style="font-size:13px;font-weight:600;color:#0f2d5e;margin-bottom:12px">Complete this work order</div>

            <form method="POST" action="{{ route('technician.workorders.complete', $workOrder) }}">
                @csrf
                @method('PATCH')

                <div class="fg">
                    <label>Resolution notes *</label>
                    <textarea name="resolution_notes" placeholder="What was done to resolve this issue?" required></textarea>
                </div>

                <div class="fg2">
                    <div class="fg">
                        <label>Asset condition after repair *</label>
                        <select name="condition" required>
                            <option value="">Select condition</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="unserviceable">Unserviceable</option>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Actual cost (₱)</label>
                        <input type="number" step="0.01" name="actual_cost" placeholder="0.00">
                    </div>
                </div>

                <div style="background:#e3f2fd;border-radius:6px;padding:8px 11px;margin-bottom:14px;font-size:12px;color:#185fa5">
                    <i class="ti ti-info-circle" style="font-size:13px"></i>
                    Marking this complete will update the asset's condition and notify the person who reported it.
                </div>

                <button type="submit" class="btn pri sm">
                    <i class="ti ti-check" style="font-size:13px"></i> Mark complete
                </button>
            </form>
        </div>
    @elseif ($workOrder->status === 'completed')
        <div class="card" style="background:#f0fdf4;border-color:#c0dd97">
            <div style="font-size:13px;font-weight:600;color:#2d7d32;margin-bottom:6px">
                <i class="ti ti-circle-check" style="font-size:15px"></i> Completed {{ $workOrder->completed_at->format('M j, Y g:i A') }}
            </div>
            <div style="font-size:12.5px;color:#334155">{{ $workOrder->resolution_notes }}</div>
        </div>
    @endif

</x-prototype-layout>
