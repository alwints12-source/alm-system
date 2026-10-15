<x-prototype-layout title="Asset details">

    @php $asset = $assignment->asset; @endphp

    <div class="ph">
        <div class="ph-l">
            <div class="bc">My workspace</div>
            <div class="pt">{{ $asset->name }}</div>
        </div>
        <a href="{{ route('holder.assets.index') }}" class="btn sm" style="background:#f1f5f9;color:#334155">
            <i class="ti ti-arrow-left" style="font-size:13px"></i> Back to my assets
        </a>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div style="font-size:13px;font-weight:600;color:#334155;margin-bottom:12px">Asset details</div>
        <div class="fg2" style="margin-bottom:0">
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Asset tag</div>
                <div style="font-size:13px">{{ $asset->asset_tag }}</div>
            </div>
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Category</div>
                <div style="font-size:13px">{{ $asset->category->name ?? 'Uncategorised' }}</div>
            </div>
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Location</div>
                <div style="font-size:13px">{{ $asset->location->name ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Serial no.</div>
                <div style="font-size:13px">{{ $asset->serial_number ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Condition</div>
                <div style="font-size:13px;text-transform:capitalize">{{ $asset->condition }}</div>
            </div>
            <div>
                <div style="font-size:10.5px;color:#94a3b8;text-transform:uppercase;font-weight:600;margin-bottom:2px">Assigned date</div>
                <div style="font-size:13px">{{ $assignment->assigned_at->format('M j, Y') }}</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div style="font-size:13px;font-weight:600;color:#334155;margin-bottom:12px">Photos</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px">
            @forelse ($asset->media as $photo)
                <div style="position:relative;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
                    <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?? $asset->name }}" style="width:100%;height:110px;object-fit:cover;display:block">
                    @if ($photo->is_primary)
                        <span style="position:absolute;top:6px;left:6px;background:#2d7d32;color:#fff;font-size:10px;padding:2px 6px;border-radius:10px">Primary</span>
                    @endif
                    @if ($photo->caption)
                        <div style="font-size:10.5px;color:#64748b;padding:4px 6px">{{ $photo->caption }}</div>
                    @endif
                </div>
            @empty
                <div style="font-size:12px;color:#94a3b8;grid-column:1/-1">No photos for this asset yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div style="font-size:13px;font-weight:600;color:#334155;margin-bottom:12px">Repair history ({{ $asset->workOrders->count() }})</div>
        @forelse ($asset->workOrders as $wo)
            <div style="background:#f8fafc;border-radius:6px;padding:9px 12px;margin-bottom:6px;font-size:12px">
                <div style="display:flex;justify-content:space-between">
                    <strong>{{ $wo->title }}</strong>
                    <span style="color:#94a3b8">{{ $wo->completed_at->format('M j, Y') }}</span>
                </div>
                <div style="color:#64748b;font-size:11.5px;margin-top:2px">
                    Fixed by {{ $wo->assignedTo->first_name ?? '' }} {{ $wo->assignedTo->last_name ?? '' }}
                    @if ($wo->resolution_notes)
                        — {{ $wo->resolution_notes }}
                    @endif
                </div>
            </div>
        @empty
            <div style="font-size:12px;color:#94a3b8">No completed repairs for this asset.</div>
        @endforelse
    </div>

</x-prototype-layout>
