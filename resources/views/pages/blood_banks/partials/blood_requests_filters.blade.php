@php
    $bloodComponentTypes = ['Fresh', 'RBC', 'PRBC', 'Platelets', 'Plasma', 'Whole Blood'];
@endphp

<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bx bx-filter-alt me-1"></i>{{ localize('global.filters') }}
        </h6>
    </div>
    <div class="card-body">
        <form method="get" action="{{ url()->current() }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ localize('global.search') }}</label>
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       class="form-control form-control-sm"
                       placeholder="{{ localize('global.patient_name') }} / {{ localize('global.card_number') }} / {{ localize('global.phone') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ localize('global.requested_department') }}</label>
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">{{ localize('global.all') }}</option>
                    @foreach ($departments ?? [] as $d)
                        <option value="{{ $d->id }}" @selected((string) request('department_id') === (string) $d->id)>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-0">{{ localize('global.blood_group') }}</label>
                <select name="group" class="form-select form-select-sm">
                    <option value="">{{ localize('global.all') }}</option>
                    @foreach (['A', 'B', 'AB', 'O'] as $g)
                        <option value="{{ $g }}" @selected(request('group') === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-0">{{ localize('global.rh') }}</label>
                <select name="rh" class="form-select form-select-sm">
                    <option value="">{{ localize('global.all') }}</option>
                    <option value="+" @selected(request('rh') === '+')>+</option>
                    <option value="-" @selected(request('rh') === '-')>-</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ localize('global.blood_type') }}</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">{{ localize('global.all') }}</option>
                    @foreach ($bloodComponentTypes as $t)
                        <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-0">{{ localize('global.from') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-0">{{ localize('global.to') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-12 col-lg-auto d-flex flex-wrap gap-2 align-items-end">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bx bx-search me-1"></i>{{ localize('global.filter') }}
                </button>
                <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-reset me-1"></i>{{ localize('global.reset') }}
                </a>
            </div>
        </form>
    </div>
</div>
