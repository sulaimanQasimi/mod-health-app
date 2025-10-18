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
                    <h5 class="mb-0">{{ localize('global.department_details') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a href="{{ route('departments.index') }}" class="btn btn-secondary me-2">
                            <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                        </a>
                        @can('edit-departments')
                        <a href="{{ route('departments.edit', $department) }}" class="btn btn-primary">
                            <i class="bx bx-edit me-1"></i>{{ localize('global.edit') }}
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.name') }}</label>
                                <p class="form-control-plaintext">{{ $department->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.category') }}</label>
                                <p class="form-control-plaintext">{{ $department->category->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.created_at') }}</label>
                                <p class="form-control-plaintext">{{ $department->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.updated_at') }}</label>
                                <p class="form-control-plaintext">{{ $department->updated_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                    @if($department->sections->count() > 0)
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.related_sections') }}</label>
                                <div>
                                    @foreach ($department->sections as $section)
                                        <span class="badge bg-primary me-1">{{ $section->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
