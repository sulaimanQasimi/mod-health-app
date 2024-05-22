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
                        <h5 class="mb-0">{{ localize('global.create_doctor') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('doctors.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name">{{localize('global.name')}}</label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="branch_id">{{localize('global.branch')}}</label>
                                        <select class="form-control select2" name="branch_id" id="branch_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($branches as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department_id">{{localize('global.department')}}</label>
                                        <select class="form-control select2" name="department_id" id="department_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($departments as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="section_id">{{localize('global.section')}}</label>
                                        <select class="form-control select2" name="section_id" id="section_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($sections as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="branch_id" value="{{Auth::user()->branch_id}}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{localize('global.create')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')

<script>
    $(document).ready(function()
{
        $('#branch_id').on('change', function()
    {
        var branchID = $(this).val();
        if(branchID !== '')
        {
            $.ajax({
                url: '/get_departments/' + branchID,
                type: 'GET',
                success: function(response)
                {

                    $('#department_id').html(response);
                }
            })
        }
    })

    $('#department_id').on('change', function()
    {
        var depID = $(this).val();
        if(depID !== '')
        {
            $.ajax({
                url: '/get_sections/' + depID,
                type: 'GET',
                success: function(response)
                {

                    $('#section_id').html(response);
                }
            })
        }
    })
})
</script>

@endsection
