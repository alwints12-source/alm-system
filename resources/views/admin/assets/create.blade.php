<x-prototype-layout title="Register new asset">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Administrative Admin › Asset Registration</div>
            <div class="pt">Register new asset</div>
        </div>
    </div>

    @if ($errors->any())
        <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.assets.store') }}">
        @csrf

        <div class="card">
            <div style="font-size:13px;font-weight:600;color:#0f2d5e;margin-bottom:12px;display:flex;align-items:center;gap:7px">
                <i class="ti ti-info-circle" style="font-size:15px;color:#4a9eff"></i> Asset information
            </div>

            <div class="fg2">
                <div class="fg">
                    <label>Asset name *</label>
                    <input type="text" name="name" placeholder="e.g. Dell Latitude 5540" value="{{ old('name') }}" required>
                </div>
                <div class="fg">
                    <label>Asset type *</label>
                    <select name="category_id" id="category-select" required onchange="applyCategoryDefaults()">
                        <option value="">Select type</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                data-life="{{ $category->default_useful_life_yrs }}"
                                data-rate="{{ $category->default_salvage_rate }}"
                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="fg">
                    <label>Location / Department</label>
                    <select name="location_id">
                        <option value="">Select location</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="fg">
                    <label>Serial number</label>
                    <input type="text" name="serial_number" placeholder="e.g. SN-2024-00123" value="{{ old('serial_number') }}">
                </div>
                <div class="fg">
                    <label>Purchase date *</label>
                    <input type="date" name="acquisition_date" value="{{ old('acquisition_date') }}" required>
                </div>
                <div class="fg">
                    <label>Acquisition cost (₱) *</label>
                    <input type="number" step="0.01" name="acquisition_cost" id="cost-input" placeholder="0.00" value="{{ old('acquisition_cost') }}" required oninput="recalculateSalvage()">
                </div>
                <div class="fg">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="in_storage" {{ old('status') == 'in_storage' ? 'selected' : '' }}>In storage</option>
                        <option value="in_maintenance" {{ old('status') == 'in_maintenance' ? 'selected' : '' }}>Under repair</option>
                        <option value="disposed" {{ old('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Supplier / Vendor</label>
                    <input type="text" name="supplier" placeholder="e.g. ABC Technologies Inc." value="{{ old('supplier') }}">
                </div>
                <div class="fg">
                    <label>Warranty expiry</label>
                    <input type="date" name="warranty_expiry_date" value="{{ old('warranty_expiry_date') }}">
                </div>
            </div>

            <div style="border-top:0.5px solid #e5e9f0;margin:18px 0 16px"></div>
            <div style="font-size:13px;font-weight:600;color:#0f2d5e;margin-bottom:12px;display:flex;align-items:center;gap:7px">
                <i class="ti ti-chart-line" style="font-size:15px;color:#4a9eff"></i> Depreciation
            </div>

            <div class="fg2">
                <div class="fg">
                    <label>Useful life (years) *</label>
                    <input type="number" name="useful_life_years" id="life-input" min="1" value="{{ old('useful_life_years') }}" required oninput="checkLifeWarning()">
                    <div style="font-size:11px;color:#94a3b8;margin-top:4px" id="life-suggestion-note"></div>
                </div>
                <div class="fg">
                    <label>Salvage value (₱)</label>
                    <input type="number" step="0.01" name="salvage_value" id="salvage-input" min="0" value="{{ old('salvage_value', 0) }}">
                    <div style="font-size:11px;color:#94a3b8;margin-top:4px">Suggested from category — adjust if this item differs.</div>
                </div>
            </div>

            <div id="life-warning" style="display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:8px 11px;margin-bottom:14px;font-size:12px;color:#7a4a0a">
                <i class="ti ti-alert-triangle" style="font-size:13px"></i>
                <span id="life-warning-text"></span>
            </div>

            <div style="border-top:0.5px solid #e5e9f0;margin:18px 0 16px"></div>
            <div style="font-size:13px;font-weight:600;color:#0f2d5e;margin-bottom:12px;display:flex;align-items:center;gap:7px">
                <i class="ti ti-user-check" style="font-size:15px;color:#4a9eff"></i> Asset holder assignment
            </div>

            <div style="background:#f8fafc;border:0.5px solid #e5e9f0;border-radius:8px;padding:14px">
                <div class="fg" style="margin-bottom:0">
                    <label>Assign to user</label>
                    <select name="assign_to">
                        <option value="">— Leave unassigned for now —</option>
                        @foreach ($holders as $holder)
                            <option value="{{ $holder->id }}" {{ old('assign_to') == $holder->id ? 'selected' : '' }}>
                                {{ $holder->first_name }} {{ $holder->last_name }} ({{ $holder->department ?? 'No department set' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="background:#e3f2fd;border-radius:6px;padding:8px 11px;margin-top:10px;font-size:12px;color:#185fa5;display:flex;align-items:center;gap:7px">
                    <i class="ti ti-mail" style="font-size:13px"></i>
                    If assigned, this asset will show as "Pending" until the holder acknowledges receiving it from their dashboard.
                </div>
            </div>

            <div class="fa" style="margin-top:18px">
                <button type="submit" class="btn pri"><i class="ti ti-device-floppy" style="font-size:13px"></i> Save asset</button>
                <a href="{{ route('admin.assets.index') }}" class="btn">Cancel</a>
            </div>
        </div>
    </form>

    <script>
        let categoryDefaultLife = null;

        function applyCategoryDefaults() {
            const select = document.getElementById('category-select');
            const selectedOption = select.options[select.selectedIndex];
            const life = selectedOption.getAttribute('data-life');

            if (life) {
                categoryDefaultLife = parseInt(life);
                document.getElementById('life-input').value = categoryDefaultLife;
                document.getElementById('life-suggestion-note').textContent = `Suggested from category: ${categoryDefaultLife} years — adjust if this item differs.`;
            } else {
                categoryDefaultLife = null;
                document.getElementById('life-suggestion-note').textContent = '';
            }

            recalculateSalvage();
            checkLifeWarning();
        }

        function recalculateSalvage() {
            const select = document.getElementById('category-select');
            const selectedOption = select.options[select.selectedIndex];
            const rate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
            const cost = parseFloat(document.getElementById('cost-input').value) || 0;

            if (rate > 0 && cost > 0) {
                document.getElementById('salvage-input').value = (cost * rate).toFixed(2);
            }
        }

        function checkLifeWarning() {
            const enteredLife = parseInt(document.getElementById('life-input').value);
            const warningBox = document.getElementById('life-warning');
            const warningText = document.getElementById('life-warning-text');

            if (!categoryDefaultLife || !enteredLife) {
                warningBox.style.display = 'none';
                return;
            }

            // Flag if entered value is less than half or more than double
            // the category's typical default — catches genuine outliers
            // (a mouse under "Laptop / Desktop") without flagging normal
            // reasonable variation
            if (enteredLife < categoryDefaultLife / 2 || enteredLife > categoryDefaultLife * 2) {
                warningText.textContent = `This is quite different from the ${categoryDefaultLife}-year default for this category — double-check this is intentional for this specific item.`;
                warningBox.style.display = 'block';
            } else {
                warningBox.style.display = 'none';
            }
        }
    </script>

</x-prototype-layout>
