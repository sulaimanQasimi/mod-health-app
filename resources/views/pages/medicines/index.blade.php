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
                        <h5 class="mb-0">{{ localize('global.medicines') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-secondary create-new btn-primary" href="{{ route('medicines.create') }}"
                                type="button">
                                <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                            </a>
                        </div>
                    </div>

                    <div class="card-body">


                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.name')}}</th>
                                    <th>{{localize('global.medicine_type')}}</th>
                                    <th>{{localize('global.for_disease')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($medicines as $medicine)
                                    <tr>
                                        <td>{{ $medicine->id }}</td>
                                        <td>{{ $medicine->name }}</td>
                                        <td>{{ $medicine->medicineType->type }}</td>
                                        <td>
                                            @foreach ($medicine->getAssociatedDiseaseAttribute() as $disease)
                                                <span class="badge bg-primary">{{ $disease->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="{{route('medicines.edit', $medicine->id)}}"><span><i class="bx bx-edit"></i></span></a>
                                            {{-- <form action="{{ route('medicines.destroy', $medicine) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this medicine?')">Delete</button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-2">
                            {{ $medicines->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
