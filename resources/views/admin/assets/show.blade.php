<x-prototype-layout title="Asset detail">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Administrative Admin</div>
            <div class="pt">{{ $asset->name }}</div>
        </div>
        <a href="{{ route('admin.assets.index') }}" class="btn sm" style="background:#f1f5f9;color:#334155">
            <i class="ti ti-arrow-left" style="font-size:13px"></i> Back to inventory
        </a>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    @error('photo')
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ $message }}
        </div>
    @enderror

    @php
        $statusClass = match($asset->status) {
            'active' => 'b-act',
            'in_maintenance' => 'b-maint',
            'in_storage' => 'b-pend',
            'disposed' => 'b-canc',
            default => '',
        };
        $statusLabel = match($asset->status) {
            'active' => 'Active',
            'in_maintenance' => 'Under repair',
            'in_storage' => 'In storage',
            'disposed' => 'Disposed',
            default => ucfirst($asset->status),
        };
        $assignment = $asset->currentAssignment;
    @endphp

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
        <div class="tw" style="padding:18px">
            <div style="font-size:13px;font-weight:600;color:#334155;margin-bottom:12px">Asset details</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12.5px">
                <div><span style="color:#64748b">Tag</span><br>{{ $asset->asset_tag }}</div>
                <div><span style="color:#64748b">Status</span><br><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></div>
                <div><span style="color:#64748b">Category</span><br>{{ $asset->category->name ?? '—' }}</div>
                <div><span style="color:#64748b">Location</span><br>{{ $asset->location->name ?? '—' }}</div>
                <div><span style="color:#64748b">Serial number</span><br>{{ $asset->serial_number ?? '—' }}</div>
                <div><span style="color:#64748b">Acquisition cost</span><br>₱{{ number_format($asset->acquisition_cost, 2) }}</div>
            </div>
        </div>

        <div class="tw" style="padding:18px">
            <div style="font-size:13px;font-weight:600;color:#334155;margin-bottom:12px">Assigned holder</div>
            @if ($assignment)
                @php
                    $holder = $assignment->holder;
                    $initials = strtoupper(substr($holder->first_name, 0, 1) . substr($holder->last_name, 0, 1));
                @endphp
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="ava ava-sm">{{ $initials }}</div>
                    <div>
                        <div style="font-size:12.5px;font-weight:500">{{ $holder->first_name }} {{ $holder->last_name }}</div>
                        <div style="font-size:11px;color:#64748b">{{ $assignment->status === 'acknowledged' ? 'Confirmed' : 'Pending acknowledgement' }}</div>
                    </div>
                </div>
            @else
                <span style="font-size:11.5px;color:#854f0b;background:#fffbeb;padding:2px 8px;border-radius:20px;font-weight:500;border:0.5px solid #fde68a">⚠ Unassigned</span>
            @endif
        </div>
    </div>

    <div class="tw" style="padding:18px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
            <div style="font-size:13px;font-weight:600;color:#334155">Photos</div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:16px">
            @forelse ($asset->media as $photo)
                <div style="position:relative;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
                    <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?? $asset->name }}" style="width:100%;height:110px;object-fit:cover;display:block">
                    @if ($photo->is_primary)
                        <span style="position:absolute;top:6px;left:6px;background:#2d7d32;color:#fff;font-size:10px;padding:2px 6px;border-radius:10px">Primary</span>
                    @endif
                    <form action="{{ route('admin.media.delete', $photo) }}" method="POST" onsubmit="return confirm('Remove this photo?')" style="position:absolute;top:6px;right:6px">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:rgba(0,0,0,0.55);color:#fff;border:none;border-radius:6px;width:22px;height:22px;font-size:12px;cursor:pointer">✕</button>
                    </form>
                    @if ($photo->caption)
                        <div style="font-size:10.5px;color:#64748b;padding:4px 6px">{{ $photo->caption }}</div>
                    @endif
                </div>
            @empty
                <div style="font-size:12px;color:#94a3b8;grid-column:1/-1">No photos uploaded yet.</div>
            @endforelse
        </div>

        <form action="{{ route('admin.assets.uploadPhoto', $asset) }}" method="POST" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;border-top:1px solid #e2e8f0;padding-top:14px">
            @csrf
            <div style="flex:1;min-width:180px">
                <label style="font-size:11.5px;color:#64748b;display:block;margin-bottom:4px">Photo (JPG/PNG/WEBP, max 5MB)</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required style="font-size:12.5px">
            </div>
            <div style="flex:1;min-width:180px">
                <label style="font-size:11.5px;color:#64748b;display:block;margin-bottom:4px">Caption (optional)</label>
                <input type="text" name="caption" maxlength="255" class="fs" style="width:100%">
            </div>
            <button type="submit" class="btn pri sm">
                <i class="ti ti-upload" style="font-size:13px"></i> Upload
            </button>
        </form>
    </div>

</x-prototype-layout>
