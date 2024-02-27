<li class="permission-item">
    <div class="d-flex">
        <span class="main-folder-icon bx bx-folder"></span>
        <label class="form-check">
            <input type="checkbox" name="permission[]" value="{{ $permission->id }}"
                   @if (isset($role->permissions)) {{ in_array($permission->id, $role->permissions->pluck('id')->toArray()) ? 'checked' : '' }} @endif>
            {{ $permission->name_dr }}
        </label>
    </div>
    @if ($permission->sub_permissions)
        <ul>
            @foreach ($permission->sub_permissions as $subPermission)
                <li class="sub-permission-item">
                    <div class="d-flex">
                        <span class="sub-folder-icon bx bx-folder"></span>
                        <label class="form-check">
                            <input type="checkbox" name="permission[]" value="{{ $subPermission->id }}"
                                   @if (isset($role->permissions)) {{ in_array($subPermission->id, $role->permissions->pluck('id')->toArray()) ? 'checked' : '' }} @endif>
                            {{ $subPermission->name_dr }}
                        </label>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</li>
