<x-prototype-layout title="New transfer">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Lifecycle</div>
            <div class="pt">New transfer request</div>
        </div>
    </div>

    <div class="card" style="max-width:600px">
        @if ($errors->any())
            <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.transfers.store') }}">
            @csrf

            <div class="fg">
                <label>Asset to transfer *</label>
                <select name="asset_id" required>
                    <option value="">Select asset</option>
                    @foreach ($assignedAssets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->asset_tag }} — {{ $asset->name }}
                            (currently: {{ $asset->currentAssignment->holder->first_name ?? '' }} {{ $asset->currentAssignment->holder->last_name ?? '' }})
                        </option>
                    @endforeach
                </select>
                @if ($assignedAssets->isEmpty())
                    <div style="font-size:11px;color:#94a3b8;margin-top:4px">No currently-assigned assets available to transfer.</div>
                @endif
            </div>

            <div class="fg">
                <label>Transfer to *</label>
                <select name="to_holder_id" required>
                    <option value="">Select new holder</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ \Illuminate\Support\Str::headline($user->role) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="fg">
                <label>New location (optional — leave blank to keep current)</label>
                <select name="to_location_id">
                    <option value="">— Keep current location —</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fg">
                <label>Reason for transfer *</label>
                <textarea name="reason" placeholder="Why is this asset being reassigned?" required></textarea>
            </div>

            <div style="background:#e3f2fd;border-radius:6px;padding:8px 11px;margin-bottom:14px;font-size:12px;color:#185fa5">
                <i class="ti ti-info-circle" style="font-size:13px"></i>
                This creates a pending request. Once approved, the current holder's assignment ends and the new holder must acknowledge receipt — same as a fresh assignment.
            </div>

            <div class="fa">
                <button type="submit" class="btn pri sm">Submit request</button>
                <a href="{{ route('admin.transfers.index') }}" class="btn sm">Cancel</a>
            </div>
        </form>
    </div>

</x-prototype-layout>
