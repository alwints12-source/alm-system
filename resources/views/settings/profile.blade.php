<x-prototype-layout title="Settings">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Settings</div>
            <div class="pt">My profile</div>
        </div>
    </div>

    @include('settings._tabs', ['active' => 'profile'])

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    <div class="card" style="max-width:620px">
        <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:4px">Account details</div>
        <div style="font-size:12.5px;color:#64748b;margin-bottom:16px">
            Your name, email, role and department are set by the Technical Admin. You can update your contact number.
        </div>

        @if ($errors->any())
            <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('settings.profile.update') }}">
            @csrf
            @method('PATCH')

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="fg"><label>First name</label><input type="text" value="{{ $user->first_name }}" disabled></div>
                <div class="fg"><label>Last name</label><input type="text" value="{{ $user->last_name }}" disabled></div>
                <div class="fg"><label>Email</label><input type="text" value="{{ $user->email }}" disabled></div>
                <div class="fg"><label>Role</label><input type="text" value="{{ \Illuminate\Support\Str::headline($user->role) }}" disabled></div>
                <div class="fg"><label>Department</label><input type="text" value="{{ $user->department ?? '—' }}" disabled></div>
                <div class="fg"><label>Position</label><input type="text" value="{{ $user->position ?? '—' }}" disabled></div>
                <div class="fg"><label>Employee ID</label><input type="text" value="{{ $user->employee_id ?? '—' }}" disabled></div>
                <div class="fg"><label>Contact number</label><input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" maxlength="30" placeholder="e.g. +63 912 345 6789"></div>
            </div>

            <button type="submit" class="btn pri sm" style="margin-top:6px">
                <i class="ti ti-check" style="font-size:13px"></i> Save changes
            </button>
        </form>
    </div>

</x-prototype-layout>
