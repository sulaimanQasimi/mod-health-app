@extends('layouts.master')

@php
    $hasMorphable = $morphableType && $morphableId;
    $pageTitle = $hasMorphable ? localize('global.manage_vital_signs') : localize('global.create_vital_sign');
    $backUrl = null;
    if ($hasMorphable) {
        $backUrl =
            $morphableType === 'App\\Models\\Hospitalization'
                ? route('hospitalizations.show', $morphableId)
                : route('under_reviews.show', $morphableId);
    }
@endphp

@section('title', $pageTitle)

@section('content')
    <table class="table table-bordered">
        <tr>
            <td colspan="3">
                <input type="date" class="form-control dari-datepicker">
            </td>
        </tr>
        <tr>
            <td>
                <div class="vital-sign-block">
                    <div class="card-header">
                        <h5 class="card-title">Vital Sign</h5>
                    </div>
                </div>
            </td>
            <td>
                <div class="vital-sign-block">
                    <div class="card-header">
                        <h5 class="card-title">Morning Time</h5>
                    </div>
                </div>
            </td>
            <td>
                <div class="vital-sign-block">
                    <div class="card-header">
                        <h5 class="card-title">Evening Time</h5>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="vital-sign-block">
                    <select class="form-control">
                        @foreach ($vitalSignTypes as $vitalSignType)
                            <option value="{{ $vitalSignType->id }}">{{ $vitalSignType->name }}</option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td>
                <input type="time" class="form-control">
            </td>
            <td>
                <input type="time" class="form-control">
            </td>

        </tr>
    </table>
    <button class="btn btn-primary add-row">Save</button>
    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.dari-datepicker').datepicker({
                    format: 'yyyy/mm/dd',
                });
                $('.add-row').click(function() {
                    $('.table-bordered').append(`
             <tr>
        <td>
            <div class="vital-sign-block">
                <select class="form-control">
                    @foreach ($vitalSignTypes as $vitalSignType)
                        <option value="{{ $vitalSignType->id }}">{{ $vitalSignType->name }}</option>
                    @endforeach
                </select>
            </div>
        </td>
        <td>
            <input type="time" class="form-control">
        </td>
        <td>
            <input type="time" class="form-control">
        </td>

    </tr>

            `);
                });
            });
        </script>
    @endpush
@endsection
