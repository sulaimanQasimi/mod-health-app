@extends('layouts.master')
<title>{{ localize('global.home_page') }}</title>
@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="card h-100">

                        <div class="card-body pb-0">
                            <div id="lineChart" style="height: 400px; width:100%;"></div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
        <!-- / Content -->

        <!-- Footer -->
        @include('layouts.partial.footer')
        <!-- / Footer -->

        <div class="content-backdrop fade"></div>
    </div>
@endsection
