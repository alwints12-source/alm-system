@if ($errors->any())
    <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="fg">
    <label>Policy name *</label>
    <input type="text" name="name" placeholder="e.g. Critical Emergency Repairs" value="{{ old('name', $slaPolicy->name ?? '') }}" required>
</div>

<div class="fg">
    <label>Description</label>
    <textarea name="description" placeholder="What this policy covers and why...">{{ old('description', $slaPolicy->description ?? '') }}</textarea>
</div>

<div class="fg2">
    <div class="fg">
        <label>Priority *</label>
        <select name="priority" required>
            <option value="">Select priority</option>
            @foreach (['critical', 'high', 'medium', 'low'] as $p)
                <option value="{{ $p }}" {{ old('priority', $slaPolicy->priority ?? '') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>
    <div class="fg">
        <label>Maintenance type</label>
        <select name="maintenance_type">
            <option value="">— Any type —</option>
            @foreach (['corrective', 'preventive', 'predictive', 'emergency'] as $t)
                <option value="{{ $t }}" {{ old('maintenance_type', $slaPolicy->maintenance_type ?? '') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="fg">
    <label>Asset category</label>
    <select name="category_id">
        <option value="">— Any category —</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" {{ old('category_id', $slaPolicy->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
        @endforeach
    </select>
</div>

<div style="background:#e3f2fd;border-radius:6px;padding:8px 11px;margin-bottom:14px;font-size:12px;color:#185fa5">
    <i class="ti ti-info-circle" style="font-size:13px"></i>
    Leave type and/or category blank for a broader policy that applies more widely. A specific match (priority + type + category) always takes precedence over a broader one at the same priority.
</div>

<div class="fg2">
    <div class="fg">
        <label>Response time target (hours) *</label>
        <input type="number" name="response_time_hours" min="1" value="{{ old('response_time_hours', $slaPolicy->response_time_hours ?? '') }}" required>
    </div>
    <div class="fg">
        <label>Resolution time target (hours) *</label>
        <input type="number" name="resolution_time_hours" min="1" value="{{ old('resolution_time_hours', $slaPolicy->resolution_time_hours ?? '') }}" required>
    </div>
</div>
