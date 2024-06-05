@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.prescription_details') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
                                <span class="text-white"> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">


                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                    <tr>
                                        <td>{{ $prescription->id }}</td>
                                        <td>{{ $prescription->patient->name }}</td>
                                        <td>
                                            @if ($prescription->is_completed == '0')
                                                <span
                                                    class="badge bg-danger">{{ localize('global.not_delivered') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-success">{{ localize('global.delivered') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($prescription->is_completed == '0')
                                            <div class="d-flex justify-content-center text-center mt-2">
                                                <form action="{{ route('prescriptions.changeStatus', $prescription) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_completed" value="1">
                                                    <button type="submit" class="btn btn-success">
                                                        <span><i class="bx bx-check-shield"></i></span>
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                
                            </tbody>
                        </table>
                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                            class="bx bx-notepad p-1"></i>{{ localize('global.prescription_details') }}</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.dosage') }}</th>
                                    <th>{{ localize('global.frequency') }}</th>
                                    <th>{{ localize('global.amount') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prescription->prescriptionItems as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->medicine_id }}</td>
                                    <td>{{ $item->medicine_type_id }}</td>
                                    <td>{{ $item->dosage }}</td>
                                    <td>{{ $item->frequency }}</td>
                                    <td>{{ $item->amount }}</td>
                                    <td>
                                        <span><i
                                                class="{{ $item->is_delivered == 0 ? 'bx bx-x-circle text-danger' : 'bx bx-check-circle text-success' }}"></i>
                                                @if($item->is_delivered == '0')

                                                <form action="{{ route('prescription_items.changeStatus', $item) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_delivered" value="1">
                                                    <button type="submit" class="btn btn-success">
                                                        <span><i class="bx bx-check-shield"></i></span>
                                                    </button>
                                                </form>
                                            @endif
                                            </span>
                                                
                                    </td>
                                </tr>
                                @endforeach
                        
                            </tbody>
                        </table>
                        
                        </form>
                        </button>
                    </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection

