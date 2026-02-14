{{-- Advanced filter for operations list. Expects: $filterRoute (route name), $branches, $departments, $operationTypes, $surgeons --}}
<div class="card-body border-bottom">
    <form method="GET" action="{{ route($filterRoute) }}" class="row g-3" id="operations-filter-form">
        <div class="col-md-2">
            <label for="search" class="form-label">{{ localize('global.search') }}</label>
            <input type="text" class="form-control" id="search" name="search"
                   value="{{ request('search') }}" placeholder="{{ localize('global.patient_name') }} / {{ localize('global.card_number') }}">
        </div>
        <div class="col-md-2">
            <label for="branch_id" class="form-label">{{ localize('global.branch') }}</label>
            <select class="form-select" id="branch_id" name="branch_id">
                <option value="">{{ localize('global.all') }}</option>
                @foreach($branches ?? [] as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="department_id" class="form-label">{{ localize('global.department') }}</label>
            <select class="form-select" id="department_id" name="department_id">
                <option value="">{{ localize('global.all') }}</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="operation_type_id" class="form-label">{{ localize('global.operation_type') }}</label>
            <select class="form-select" id="operation_type_id" name="operation_type_id">
                <option value="">{{ localize('global.all') }}</option>
                @foreach($operationTypes ?? [] as $type)
                    <option value="{{ $type->id }}" {{ request('operation_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="surgeon_id" class="form-label">{{ localize('global.operation_surgion') }}</label>
            <select class="form-select" id="surgeon_id" name="surgeon_id">
                <option value="">{{ localize('global.all') }}</option>
                @foreach($surgeons ?? [] as $doctor)
                    <option value="{{ $doctor->id }}" {{ request('surgeon_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="date_from" class="form-label">{{ localize('global.date_from') }}</label>
            <input type="text" class="form-control datepicker_dari" id="date_from" name="date_from"
                   value="{{ request('date_from') }}" placeholder="1403/01/01" autocomplete="off">
        </div>
        <div class="col-md-2">
            <label for="date_to" class="form-label">{{ localize('global.date_to') }}</label>
            <input type="text" class="form-control datepicker_dari" id="date_to" name="date_to"
                   value="{{ request('date_to') }}" placeholder="1403/01/01" autocomplete="off">
        </div>
        <div class="col-md-2">
            <label for="per_page" class="form-label">{{ localize('global.per_page') }}</label>
            <select class="form-select" id="per_page" name="per_page">
                @foreach([10, 15, 25, 50, 100] as $n)
                    <option value="{{ $n }}" {{ request('per_page', 15) == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="sort_by" class="form-label">{{ localize('global.sort_by') }}</label>
            <select class="form-select" id="sort_by" name="sort_by">
                <option value="date" {{ request('sort_by', 'date') == 'date' ? 'selected' : '' }}>{{ localize('global.date') }}</option>
                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>{{ localize('global.created_at') }}</option>
                <option value="time" {{ request('sort_by') == 'time' ? 'selected' : '' }}>{{ localize('global.time') }}</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="sort_order" class="form-label">{{ localize('global.order') }}</label>
            <select class="form-select" id="sort_order" name="sort_order">
                <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>{{ localize('global.descending') }}</option>
                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>{{ localize('global.ascending') }}</option>
            </select>
        </div>
        <div class="col-12 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-search"></i> {{ localize('global.search') }}
            </button>
            <a href="{{ route($filterRoute) }}" class="btn btn-secondary">
                <i class="bx bx-refresh"></i> {{ localize('global.clear') }}
            </a>
        </div>
    </form>
</div>
