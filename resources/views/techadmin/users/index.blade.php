<x-prototype-layout title="User management">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Technical Admin</div>
            <div class="pt">User management</div>
        </div>
        <button class="btn pri sm" onclick="document.getElementById('modal-adduser').style.display='flex'">
            <i class="ti ti-plus" style="font-size:13px"></i> Add user
        </button>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    <div class="fr">
        <input class="fs" type="text" placeholder="Search users..." id="user-search" onkeyup="filterUsers()">
        <select id="role-filter" onchange="filterUsers()">
            <option value="">All roles</option>
            <option value="administrative_admin">Administrative Admin</option>
            <option value="technical_admin">Technical Admin</option>
            <option value="asset_holder">Asset Holder</option>
            <option value="technician">Technician</option>
        </select>
        <select id="status-filter" onchange="filterUsers()">
            <option value="">All status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th style="width:150px">Name</th>
                    <th style="width:200px">Email</th>
                    <th style="width:130px">Role</th>
                    <th style="width:120px">Department</th>
                    <th style="width:80px">Status</th>
                    <th style="width:110px">Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                @foreach ($users as $user)
                    @php
                        $roleClass = match($user->role) {
                            'administrative_admin' => 'r-aa',
                            'technical_admin' => 'r-ta',
                            'asset_holder' => 'r-ah',
                            'technician' => 'r-tc',
                            default => '',
                        };
                        $roleLabel = \Illuminate\Support\Str::headline($user->role);
                        $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
                    @endphp
                    <tr data-role="{{ $user->role }}" data-status="{{ $user->is_active ? 'active' : 'inactive' }}" data-search="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:7px">
                                <div class="ava ava-sm">{{ $initials }}</div>
                                {{ $user->first_name }} {{ $user->last_name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge {{ $roleClass }}">{{ $roleLabel }}</span></td>
                        <td>{{ $user->department }}</td>
                        <td>
                            @if ($user->is_active)
                                <span class="badge b-act">Active</span>
                            @else
                                <span class="badge b-canc">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="ab">
                                <form method="POST" action="{{ route('techadmin.users.toggle', $user) }}" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ac {{ $user->is_active ? 'd' : 'e' }}">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ADD USER MODAL --}}
    <div id="modal-adduser" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-hdr">
                <div class="modal-ttl">Add new user</div>
                <button class="modal-close" onclick="document.getElementById('modal-adduser').style.display='none'">&times;</button>
            </div>

            <form method="POST" action="{{ route('techadmin.users.store') }}">
                @csrf

                <div class="fg2">
                    <div class="fg">
                        <label>First name *</label>
                        <input type="text" name="first_name" placeholder="e.g. Juan" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="fg">
                        <label>Last name *</label>
                        <input type="text" name="last_name" placeholder="e.g. Dela Cruz" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="fg">
                    <label>Email address *</label>
                    <input type="email" name="email" placeholder="user@dost-pes.gov.ph" value="{{ old('email') }}" required>
                </div>

                <div class="fg2">
                    <div class="fg">
                        <label>Role *</label>
                        <select name="role" required>
                            <option value="">Select role</option>
                            <option value="administrative_admin">Administrative Admin</option>
                            <option value="technical_admin">Technical Admin</option>
                            <option value="asset_holder">Asset Holder</option>
                            <option value="technician">Technician</option>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Department *</label>
                        <select name="department" required>
                            <option value="">Select department</option>
                            <option value="ICT Division">ICT Division</option>
                            <option value="Administrative Office">Administrative Office</option>
                            <option value="Research Division">Research Division</option>
                            <option value="Operations">Operations</option>
                        </select>
                    </div>
                </div>

                <div class="fg2">
                    <div class="fg">
                        <label>Temporary password *</label>
                        <input type="password" name="password" placeholder="Set a temporary password" required>
                    </div>
                    <div class="fg">
                        <label>Confirm password *</label>
                        <input type="password" name="password_confirmation" placeholder="Re-enter password" required>
                    </div>
                </div>

                @if ($errors->any())
                    <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="fa">
                    <button type="submit" class="btn pri sm">Create user</button>
                    <button type="button" class="btn sm" onclick="document.getElementById('modal-adduser').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterUsers() {
            const search = document.getElementById('user-search').value.toLowerCase();
            const role = document.getElementById('role-filter').value;
            const status = document.getElementById('status-filter').value;
            const rows = document.querySelectorAll('#users-table-body tr');

            rows.forEach(row => {
                const matchesSearch = row.dataset.search.includes(search);
                const matchesRole = !role || row.dataset.role === role;
                const matchesStatus = !status || row.dataset.status === status;
                row.style.display = (matchesSearch && matchesRole && matchesStatus) ? '' : 'none';
            });
        }

        @if ($errors->any())
            document.getElementById('modal-adduser').style.display = 'flex';
        @endif
    </script>

</x-prototype-layout>
