@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.edit') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.test_name') }}</th>
                                    <th>{{ localize('global.test_status') }}</th>
                                    <th>{{ localize('global.result') }}</th>
                                    <th>{{ localize('global.result_file') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lab_items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->labType->name }}</td>
                                        <td>
                                            @if ($item->status == '0')
                                                <span
                                                    class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ localize('global.tested') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->result }}</td>
                                        <td>
                                            @isset($item->result_file)
                                                <a href="{{ asset('storage/' . $item->result_file) }}" target="_blank">
                                                    <i class="fa fa-file"></i> {{ localize('global.file') }}
                                                </a>
                                            @endisset
                        
                                        </td>
                                        <td>
                                            <a href="{{ route('lab_tests.edit', $item) }}"><i class="bx bx-message-square-edit"></i></a>

                        
                                        </td>
                        
                                    </tr>
                        
                                @empty
                                    <div class="container">
                                        <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                            <div class=" badge bg-label-danger mt-4">
                                                {{ localize('global.no_previous_labs') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                        
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
