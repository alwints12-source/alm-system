<x-prototype-layout title="New disposal request">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Asset Lifecycle</div>
            <div class="pt">New disposal request</div>
        </div>
    </div>

    <div class="card" style="max-width:560px">
        @if ($errors->any())
            <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.disposals.store') }}">
            @csrf

            <div class="fg">
                <label>Asset *</label>
                <select name="asset_id" required>
                    <option value="">Select asset</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->asset_tag }} — {{ $asset->name }} ({{ ucfirst($asset->condition) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="fg">
                <label>Reason for disposal *</label>
                <textarea name="reason" placeholder="e.g. Obsolete, no longer supported, budget-driven fleet reduction..." required></textarea>
            </div>

            <div style="background:#fff0f0;border-radius:6px;padding:8px 11px;margin-bottom:14px;font-size:12px;color:#a32d2d">
                <i class="ti ti-alert-triangle" style="font-size:13px"></i>
                This is a request only — the asset stays active until a second Admin action approves it and selects a disposal method.
            </div>

            <div class="fa">
                <button type="submit" class="btn pri sm">Submit request</button>
                <a href="{{ route('admin.disposals.index') }}" class="btn sm">Cancel</a>
            </div>
        </form>
    </div>

</x-prototype-layout>
