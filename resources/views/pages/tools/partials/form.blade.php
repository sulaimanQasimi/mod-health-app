<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">{{ localize('global.name') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name', $tool->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="code">{{ localize('global.code') }}</label>
        <input type="text" name="code" id="code" value="{{ old('code', $tool->code ?? '') }}" class="form-control @error('code') is-invalid @enderror" required>
        @error('code')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="unit_id">{{ localize('global.unit') }}</label>
        <select name="unit_id" id="unit_id" class="form-select select2">
            <option value="">{{ localize('global.select_unit') }}</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $tool->unit_id ?? '') == $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="is_active">{{ localize('global.depot.is_active') }}</label>
        <select name="is_active" id="is_active" class="form-select">
            <option value="1" @selected(old('is_active', $tool->is_active ?? true))>Active</option>
            <option value="0" @selected(!old('is_active', $tool->is_active ?? true))>Inactive</option>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="description">{{ localize('global.description') }}</label>
        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $tool->description ?? '') }}</textarea>
    </div>
</div>
