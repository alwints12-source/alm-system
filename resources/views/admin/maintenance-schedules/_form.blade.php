@if ($errors->any())
    <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="fg2">
    <div class="fg">
        <label>Asset *</label>
        <select name="asset_id" required>
            <option value="">Select asset</option>
            @foreach ($assets as $asset)
                <option value="{{ $asset->id }}" {{ old('asset_id', $schedule->asset_id ?? '') == $asset->id ? 'selected' : '' }}>
                    {{ $asset->asset_tag }} — {{ $asset->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="fg">
        <label>Default technician *</label>
        <select name="default_assignee_id" required>
            <option value="">Select technician</option>
            @foreach ($technicians as $tech)
                <option value="{{ $tech->id }}" {{ old('default_assignee_id', $schedule->default_assignee_id ?? '') == $tech->id ? 'selected' : '' }}>
                    {{ $tech->first_name }} {{ $tech->last_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="fg">
    <label>Title *</label>
    <input type="text" name="title" placeholder="e.g. Quarterly Inspection" value="{{ old('title', $schedule->title ?? '') }}" required>
</div>

<div class="fg">
    <label>Description</label>
    <textarea name="description" placeholder="What this recurring task covers...">{{ old('description', $schedule->description ?? '') }}</textarea>
</div>

<div class="fg2">
    <div class="fg">
        <label>Frequency *</label>
        <select name="frequency" required>
            <option value="">Select frequency</option>
            <option value="daily" {{ old('frequency', $schedule->frequency ?? '') == 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="weekly" {{ old('frequency', $schedule->frequency ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
            <option value="monthly" {{ old('frequency', $schedule->frequency ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="quarterly" {{ old('frequency', $schedule->frequency ?? '') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
            <option value="semi_annual" {{ old('frequency', $schedule->frequency ?? '') == 'semi_annual' ? 'selected' : '' }}>Semi-annual</option>
            <option value="annual" {{ old('frequency', $schedule->frequency ?? '') == 'annual' ? 'selected' : '' }}>Annual</option>
        </select>
    </div>
    <div class="fg">
        <label>Estimated duration (hours)</label>
        <input type="number" step="0.5" name="estimated_duration_hrs" value="{{ old('estimated_duration_hrs', $schedule->estimated_duration_hrs ?? '') }}">
    </div>
</div>

<div class="fg2">
    <div class="fg">
        <label>Start date *</label>
        <input type="date" name="start_date" value="{{ old('start_date', isset($schedule) ? $schedule->start_date->toDateString() : '') }}" required>
        @if (isset($schedule))
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">Changing this won't move the next occurrence — edit that separately if needed.</div>
        @endif
    </div>
    <div class="fg">
        <label>End date (optional)</label>
        <input type="date" name="end_date" value="{{ old('end_date', isset($schedule) ? $schedule->end_date?->toDateString() : '') }}">
    </div>
</div>

<div style="background:#e3f2fd;border-radius:6px;padding:8px 11px;margin-bottom:14px;font-size:12px;color:#185fa5">
    <i class="ti ti-info-circle" style="font-size:13px"></i>
    Work orders generate automatically once due, assigned directly to the default technician — no admin review step, since this schedule itself is the approval. Rescheduling is rolling: the next occurrence is set from whenever the current one is actually completed, not from a fixed calendar.
</div>
