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

                        <h2>{{localize('global.lab_items')}}</h2>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.test_name') }}</th>
                                    <th>{{ localize('global.test_status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lab->labItems as $item)
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
                                        
                                        <td>
                                            <a href="{{ route('lab_items.updateStatus', $item->id) }}" class="btn btn-sm btn-{{ $item->status == '0' ? 'danger' : 'success' }}">
                                                @if ($item->status == '1')
                                                    <span class="bx bx-check"></span>
                                                @else
                                                <span class="bx bx-x"></span>
                                                @endif
                                            </a>
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

                        <form role="form" class="form-horizontal" action="{{ route('lab_tests.update', $lab->id) }}"
                            enctype="multipart/form-data" method="POST">
                          @method('PUT')
                          @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ localize('global.result') }}</label>
                                    <textarea name="result" class="form-control">{{ $lab->result }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ localize('global.result_file') }}</label>
                                    <input type="file" name="result_file" class="form-control">{{ $lab->result_file }}</textarea>
                                </div>
                                <input type="hidden" name="status" value="1">
                                <div class="col-md-6">
                                    <a href="{{ route('lab_tests.index') }}"><button type="button"
                                                class="btn btn-danger">{{ localize('global.back') }}</button>
                                        <button type="submit"
                                                class="btn btn-primary">{{ localize('global.save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
