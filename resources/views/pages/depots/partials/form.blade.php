@php
    $selectedUsers = collect(old('user_ids', $depot ? $depot->activeUsers->pluck('id')->all() : []))->values();
    $selectedRoles = collect(old('roles', $depot ? $depot->activeUsers->pluck('pivot.role')->all() : []))->values();
    if ($selectedUsers->isEmpty()) {
        $selectedUsers = collect(['']);
        $selectedRoles = collect(['staff']);
    }
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label for="name" class="form-label">{{ localize('global.depot.name') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name', $depot->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="address" class="form-label">{{ localize('global.depot.address') }}</label>
        <input type="text" name="address" id="address" value="{{ old('address', $depot->address ?? '') }}"
            class="form-control @error('address') is-invalid @enderror">
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="branch_id" class="form-label">{{ localize('global.depot.branch') }}</label>
        <select name="branch_id" id="branch_id" class="form-select select2 @error('branch_id') is-invalid @enderror">
            <option value="">{{ localize('global.depot.branch') }}</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', $depot->branch_id ?? '') == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="department_id" class="form-label">{{ localize('global.depot.department') }}</label>
        <select name="department_id" id="department_id" class="form-select select2 @error('department_id') is-invalid @enderror">
            <option value="">{{ localize('global.depot.department') }}</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $depot->department_id ?? '') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="pharmacy_id" class="form-label">{{ localize('global.depot.pharmacy') }}</label>
        <select name="pharmacy_id" id="pharmacy_id" class="form-select select2 @error('pharmacy_id') is-invalid @enderror">
            <option value="">{{ localize('global.depot.pharmacy') }}</option>
            @foreach($pharmacies as $pharmacy)
                <option value="{{ $pharmacy->id }}" @selected(old('pharmacy_id', $depot->pharmacy_id ?? '') == $pharmacy->id)>{{ $pharmacy->name }}</option>
            @endforeach
        </select>
        @error('pharmacy_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="parent_depot_id" class="form-label">{{ localize('global.depot.parent_depot') }}</label>
        <select name="parent_depot_id" id="parent_depot_id" class="form-select select2 @error('parent_depot_id') is-invalid @enderror">
            <option value="">{{ localize('global.depot.select_parent_depot') }}</option>
            @foreach($depots as $parentDepot)
                @if(!$depot || $parentDepot->id !== $depot->id)
                    <option value="{{ $parentDepot->id }}" @selected(old('parent_depot_id', $depot->parent_depot_id ?? '') == $parentDepot->id)>{{ $parentDepot->name }}</option>
                @endif
            @endforeach
        </select>
        @error('parent_depot_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <div class="form-check form-switch mt-4">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $depot->is_active ?? true))>
            <label class="form-check-label" for="is_active">{{ localize('global.depot.is_active') }}</label>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-check form-switch mt-4">
            <input type="hidden" name="is_base" value="0">
            <input class="form-check-input" type="checkbox" name="is_base" id="is_base" value="1" @checked(old('is_base', $depot->is_base ?? false))>
            <label class="form-check-label" for="is_base">{{ localize('global.depot.is_base') }}</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Depot Users</label>
        <div id="depot-user-container">
            @foreach($selectedUsers as $index => $selectedUserId)
                <div class="depot-user-item mb-2">
                    <div class="row g-2">
                        <div class="col-md-7">
                            <select class="form-select select2 depot-user-select" name="user_ids[]">
                                <option value="">Select user</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected($selectedUserId == $user->id)>{{ $user->name }} {{ $user->last_name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select depot-role-select" name="roles[]">
                                @foreach(['staff', 'manager', 'procurement', 'viewer'] as $role)
                                    <option value="{{ $role }}" @selected(($selectedRoles[$index] ?? 'staff') === $role)>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger remove-depot-user">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-depot-user">
            <i class="bx bx-plus"></i> Add user
        </button>
        @error('user_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('roles')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bx bx-save me-1"></i>{{ $depot ? localize('global.update') : localize('global.add') }}
    </button>
</div>
