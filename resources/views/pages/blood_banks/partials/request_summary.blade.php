{{-- Expects: $bloodBank, $bloodCheck (App\Blood\BloodCheck) --}}
<div class="row p-2 text-center">
    <div class="col-md-3 mt-2 mb-2">
        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.patient_name') }}</h5>
        <div>
            {{ $bloodCheck->patientName ?? $bloodBank->patient?->name ?? '—' }}
        </div>
    </div>
    <div class="col-md-3 mt-2 mb-2">
        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.requested_department') }}</h5>
        <div>
            {{ $bloodBank->department?->name ?? '—' }}
        </div>
    </div>
    <div class="col-md-3 mt-2 mb-2">
        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.blood_group') }}</h5>
        <div>
            @if ($bloodCheck->aboGroup == 'A')
                <span class="text-danger"><i class="fa-solid fa-a"></i></span>
            @elseif($bloodCheck->aboGroup == 'B')
                <span class="text-danger"><i class="fa-solid fa-b"></i></span>
            @elseif($bloodCheck->aboGroup == 'AB')
                <span class="text-danger" dir="ltr"><i class="fa-solid fa-a"></i><i class="fa-solid fa-b"></i></span>
            @elseif($bloodCheck->aboGroup == 'O')
                <span class="text-danger"><i class="fa-solid fa-o"></i></span>
            @endif
        </div>
    </div>
    <div class="col-md-3 mt-2 mb-2">
        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.blood_rh') }}</h5>
        <div>
            @if ($bloodCheck->rh == '+')
                <span class="bx bx-plus-circle text-danger"></span>
            @else
                <span class="bx bx-minus-circle text-danger"></span>
            @endif
        </div>
    </div>
</div>

<div class="row p-2 text-center">
    <div class="col-md-3 mt-2 mb-2">
        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.quantity') }}</h5>
        <div>
            {{ $bloodCheck->quantity }}
        </div>
    </div>
    <div class="col-md-3 mt-2 mb-2">
        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.blood_type') }}</h5>
        <div>
            {{ $bloodCheck->componentType }}
        </div>
    </div>
    <div class="col-md-3 mt-2 mb-2">
        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.created_by') }}</h5>
        <div>
            {{ $bloodBank->createdBy?->name ?? '—' }}
        </div>
    </div>
    <div class="col-md-3 mt-2 mb-2">
        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.created_at') }}</h5>
        <div dir="ltr">
            {{ \Hekmatinasser\Verta\Verta::instance($bloodBank->created_at)->format('Y/n/j H:i:s') }}
        </div>
    </div>
</div>

@if ($bloodCheck->appointmentId)
    <div class="row p-2 text-center">
        <div class="col-12 mt-2 mb-2">
            <h5 class="mb-2 bg-label-secondary p-1">{{ localize('global.appointments') }}
                #{{ $bloodCheck->appointmentId }}</h5>
            @if ($bloodBank->appointment)
                <a href="{{ route('appointments.show', $bloodCheck->appointmentId) }}"
                    class="btn btn-sm btn-outline-primary">{{ localize('global.appointment_details') }}</a>
            @endif
        </div>
    </div>
@endif

@if (count($bloodCheck->linkedContextIds(true)) > 0)
    <div class="row p-2">
        <div class="col-12">
            <h6 class="mb-2 bg-label-secondary p-2 text-center small">
                {{ localize('global.blood_check_context') }}</h6>
            <div class="d-flex flex-wrap justify-content-center gap-1">
                @foreach ($bloodCheck->linkedContextIds(true) as $label => $id)
                    <span class="badge bg-label-secondary" dir="ltr">{{ $label }}
                        #{{ $id }}</span>
                @endforeach
            </div>
        </div>
    </div>
@endif
