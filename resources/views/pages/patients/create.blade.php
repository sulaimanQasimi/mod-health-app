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
                    <h5 class="mb-0">{{ localize('global.create_patient') }}</h5>
                </div>

                <div class="card-body">
                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active fs-4"  onclick="getTab('first')" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#first"
                                    aria-controls="first" aria-selected="true">
                                    {{localize('global.mod')}}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link fs-4" role="tab" onclick="getTab('second')" data-bs-toggle="tab"
                                    data-bs-target="#second"
                                    aria-controls="second" aria-selected="false">
                                    {{localize('global.recipient')}}

                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link fs-4" role="tab" onclick="getTab('third')" data-bs-toggle="tab"
                                    data-bs-target="#third"
                                    aria-controls="third" aria-selected="false">
                                    {{localize('global.family')}}

                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="first" role="tabpanel">
                            </div>
                            <div class="tab-pane fade" id="second" role="tabpanel">
                            </div>
                            <div class="tab-pane fade" id="third" role="tabpanel">
                            </div>
                        </div>
                    </div>




                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function changeType(emp_type){
        if(emp_type == '0'){
            label = "{{ localize('global.rank') }}";
        }else{
            label = "{{ localize('global.bast') }}";
        }
        $('#rank_label').html(label);
    }
    $(document).ready(function() {
            // $('#province_id').on('change', function() {
            //     var provinceID = $(this).val();
            //     if (provinceID !== '') {
            //         $.ajax({
            //             url: '/get_districts/' + provinceID,
            //             type: 'GET',
            //             success: function(response) {

            //                 $('#district_id').html(response);
            //             }
            //         })
            //     }
            // });
            getTab('first');
        })

    function getDistricts(province_id){
        var provinceID = province_id;
                if (provinceID !== '') {
                    $.ajax({
                        url: '/get_districts/' + provinceID,
                        type: 'GET',
                        success: function(response) {

                            $('#district_id').html(response);
                        }
                    })
                }
    }

       function getTab(tab_type){
            $('#first').html('');
            $('#second').html('');
            $('#third').html('');
            $.ajax({
            url:" {{url('patients/get-tab')}}",
            type: 'GET',
            data:{tab_type:tab_type},
            success: function(data) {
                    // Update the container with the response

                    $('#'+tab_type).html(data);
                    $('.select2').select2();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }



</script>
@endsection