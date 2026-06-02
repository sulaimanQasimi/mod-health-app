<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="requesting_depot_id">Requesting Depot</label>
        <select name="requesting_depot_id" id="requesting_depot_id" class="form-select select2 @error('requesting_depot_id') is-invalid @enderror" required>
            <option value="">Select depot</option>
            @foreach($depots as $depot)
                <option value="{{ $depot->id }}" @selected(old('requesting_depot_id', request('requesting_depot_id')) == $depot->id)>{{ $depot->name }}</option>
            @endforeach
        </select>
        @error('requesting_depot_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="source_depot_id">Source Depot</label>
        <select name="source_depot_id" id="source_depot_id" class="form-select select2 @error('source_depot_id') is-invalid @enderror" required>
            <option value="">Select source depot</option>
            @foreach($depots as $depot)
                <option value="{{ $depot->id }}" @selected(old('source_depot_id') == $depot->id)>{{ $depot->name }}</option>
            @endforeach
        </select>
        @error('source_depot_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="medicine_id">{{ localize('global.medicine') }}</label>
        <select name="medicine_id" id="medicine_id" class="form-select select2 @error('medicine_id') is-invalid @enderror">
            <option value="">{{ localize('global.select_medicine') }}</option>
            @foreach($medicines as $medicine)
                <option value="{{ $medicine->id }}" @selected(old('medicine_id') == $medicine->id)>{{ $medicine->name }}</option>
            @endforeach
        </select>
        @error('medicine_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="tool_id">{{ localize('global.depot.tool') }}</label>
        <select name="tool_id" id="tool_id" class="form-select select2 @error('tool_id') is-invalid @enderror">
            <option value="">Select tool</option>
            @foreach($tools as $tool)
                <option value="{{ $tool->id }}" @selected(old('tool_id') == $tool->id)>{{ $tool->displayName() }}</option>
            @endforeach
        </select>
        @error('tool_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="quantity">{{ localize('global.quantity') }}</label>
        <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity') }}" class="form-control @error('quantity') is-invalid @enderror" required>
        @error('quantity')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="unit_id">{{ localize('global.unit') }}</label>
        <select name="unit_id" id="unit_id" class="form-select select2">
            <option value="">{{ localize('global.select_unit') }}</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="batch_number">{{ localize('global.batch_number') }}</label>
        <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number') }}" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label" for="notes">{{ localize('global.notes') }}</label>
        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const medicine = document.getElementById('medicine_id');
    const tool = document.getElementById('tool_id');
    medicine?.addEventListener('change', function () { if (this.value && tool) { tool.value = ''; if (window.jQuery) $('#tool_id').trigger('change.select2'); } });
    tool?.addEventListener('change', function () { if (this.value && medicine) { medicine.value = ''; if (window.jQuery) $('#medicine_id').trigger('change.select2'); } });
});
</script>
