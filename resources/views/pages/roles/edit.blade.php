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
                        <h5 class="mb-0">{{ localize('global.edit') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('roles.update', $role->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_en') }}</label>
                                        <input type="text" class="form-control" name="name"
                                               value="{{ $role->name }}">
                                    </div>
                                    @error('name')
                                        <div class="display-error">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_dr') }}</label>
                                        <input type="text" name="name_dr" class="form-control"
                                               value="{{ $role->name_dr }}">
                                    </div>
                                    @error('name_dr')
                                        <div class="display-error">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 mt-3 mb-3">
                                <div class="row">

                                    <div class="col-md-6">
                                        <h5>{{ localize('global.permissions_list') }}</h5>
                                        <ul id="permissions-tree">
                                            @foreach ($permissions as $value)
                                                @include('pages.permissions.sub_permissions', [
                                                    'permission' => $value,
                                                ])
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('roles.index') }}"><button type="button"
                                        class="btn btn-danger">{{ localize('global.back') }}</button>
                                <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <style>
        .main-folder-icon:before {
            content: "\eae2";
            font-family: 'boxicons';
            color: #696cff;
        }

        .sub-folder-icon:before {
            content: "\eae0";
            font-family: 'boxicons';
            color: #068a48;
        }

        .permission-item ul {
            display: none;
            list-style-type: none;
        }


        .permission-item.open ul {
            display: block;
        }

        li {
            list-style-type: none;
        }

        .form-check {
            padding-right: 0.7em !important;
        }

        .permission-item {
            position: relative;
        }

        .main-folder-icon.open:before {
            content: "\eae0";
        }
    </style>
@endpush

@push('custom-js')
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.main-folder-icon').click(function() {
                var ulElement = $(this).parent().parent().find('ul');
                ulElement.toggle();

                if (!ulElement.is(':visible')) {
                    ulElement.find('ul').hide();
                    $(this).removeClass('open'); // Remove 'open' class when closing
                } else {
                    $(this).addClass('open'); // Add 'open' class when opening
                }
            });

            $('.main-folder-icon').click();
        });
    </script>
@endpush
