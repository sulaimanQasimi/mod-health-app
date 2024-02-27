@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">{{ localize('global.my_profile') }}</h4>

            <!-- Header -->
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="user-profile-header-banner">
                            <img src="../../assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top" />
                        </div>
                        <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                            <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">

                                <img src="{{ $user->avatar != null ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}"
                                     alt="user image" class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
                            </div>
                            <div class="flex-grow-1 mt-3 mt-sm-5">
                                <div
                                     class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                                    <div class="user-profile-info">
                                        <h4>{{ $user->name_en }} ({{ $user->name_dr }})</h4>
                                        <ul
                                            class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                            <li class="list-inline-item fw-semibold"><i
                                                   class="bx bx-home"></i>{{ localize('global.sector') }}:
                                                {{ isset($user->sector->name_dr) ? $user->sector->name_dr : '' }}</li>
                                            <li class="list-inline-item fw-semibold">
                                                <i class="bx bx-calendar-alt"></i> {{ localize('global.joined_at') }}:
                                                {{ \HanifHefaz\Dcter\Dcter::GregorianToHijri($user->created_at) }}
                                            </li>
                                            <li class="list-inline-item fw-semibold">
                                                <i class="bx bx-envelope"></i> {{ localize('global.email') }}:
                                                {{ $user->email }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Header -->

            <!-- User Profile Content -->
            <div class="row">
                <div class="col-xl-4 col-lg-5 col-md-5">
                    <!-- About User -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase">{{ localize('global.about_me') }}</small>
                            <ul class="list-unstyled mb-4 mt-3">
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bx bx-user"></i><span
                                          class="fw-semibold mx-2">{{ localize('global.full_name') }}:</span>
                                    <span>{{ $user->name_dr }} {{ $user->last_name_dr }}</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bx bx-check"></i><span
                                          class="fw-semibold mx-2">{{ localize('global.status') }}:</span>
                                    <span>{{ $user->status == 0 ? localize('global.deactive') : localize('global.active') }}</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bx bx-star"></i><span
                                          class="fw-semibold mx-2">{{ localize('global.roles') }}:</span> <span>
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-primary">
                                                {{ $role->name_dr }}
                                            </span>
                                        @endforeach
                                    </span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="bx bx-envelope"></i><span
                                          class="fw-semibold mx-2">{{ localize('global.email') }}:</span>
                                    <span>{{ $user->email }}</span>
                                </li>
                            </ul>

                            <div class="d-flex justify-content-center">
                                <span class="badge bg-primary">
                                    <i class="bx bx-user display-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!--/ About User -->
                </div>
                <div class="col-xl-8 col-lg-7 col-md-7">
                    <!-- Activity Timeline -->

                    <div class="col-md-12">
                        <div class="card mb-4">
                            <h5 class="card-header">{{ localize('global.profile_avatar') }}</h5>
                            <!-- Account -->
                            <div class="card-body">
                                <div class="d-flex align-items-start align-items-sm-center gap-4">
                                    <img src="{{ $user->avatar != null ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}"
                                         alt="user-avatar" class="d-block rounded" height="100" width="100" />
                                    <form method="POST" action="{{ route('users.update-avatar') }}"
                                          enctype="multipart/form-data">
                                        @csrf
                                        <div class="d-flex justify-content-start">
                                            <div class="button-wrapper">
                                                <label for="upload" class="btn btn-primary" tabindex="0">
                                                    <input type="file" name="avatar">
                                                </label>
                                                <button type="submit"
                                                        class="btn btn-primary">{{ localize('global.save') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <hr class="my-0" />
                            <h5 class="card-header">{{ localize('global.change_password') }}</h5>
                            <div class="card-body">
                                <form method="POST" action="{{ route('users.change-password') }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label
                                                       for="current_password">{{ localize('global.current_password') }}</label>
                                                <input type="password" name="current_password" id="current_password"
                                                       class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-4">

                                            <div class="form-group">
                                                <label for="new_password">{{ localize('global.new_password') }}</label>
                                                <input type="password" name="new_password" id="new_password"
                                                       class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label
                                                       for="new_password_confirmation">{{ localize('global.password_confirmation') }}</label>
                                                <input type="password" name="new_password_confirmation"
                                                       id="new_password_confirmation" class="form-control">
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <button type="submit"
                                                    class="btn btn-primary me-2">{{ localize('global.update_password') }}</button>
                                            <button type="reset"
                                                    class="btn btn-label-secondary">{{ localize('global.cancel') }}</button>
                                        </div>
                                </form>
                            </div>
                            <!-- /Account -->
                        </div>
                    </div>

                    <!--/ Activity Timeline -->
                </div>
            </div>
            <!--/ User Profile Content -->
        </div>
        <!-- / Content -->

        <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('/assets/vendor/css/pages/page-profile.css') }}" />
@endpush

@push('custom-js')
    <script src="{{ asset('/assets/js/pages-profile.js') }}"></script>
@endpush
