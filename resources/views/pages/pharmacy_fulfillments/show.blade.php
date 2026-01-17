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
                        <h5 class="mb-0">{{ localize('global.pharmacy_fulfillment_details') }}</h5>
                        <div>
                            <a href="{{ route('pharmacy_fulfillments.edit', $pharmacyFulfillment->id) }}" class="btn btn-warning btn-sm">
                                <i class="bx bx-edit"></i> {{ localize('global.edit') }}
                            </a>
                            <a href="{{ route('pharmacy_fulfillments.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">{{ localize('global.medicine') }}</th>
                                        <td>{{ $pharmacyFulfillment->medicine->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.unit_type') }}</th>
                                        <td>{{ $pharmacyFulfillment->unit_type ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.amount') }}</th>
                                        <td><span class="badge bg-primary">{{ $pharmacyFulfillment->amount }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.form_no') }}</th>
                                        <td>{{ $pharmacyFulfillment->form_no }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.date') }}</th>
                                        <td>
                                            @if($pharmacyFulfillment->date)
                                                {{ \Verta::instance($pharmacyFulfillment->date)->formatJalaliDate() }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">{{ localize('global.pharmacy') }}</th>
                                        <td>
                                            @if($pharmacyFulfillment->pharmacy)
                                                <span class="badge bg-secondary">{{ $pharmacyFulfillment->pharmacy->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.user') }}</th>
                                        <td>{{ $pharmacyFulfillment->user->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.form') }}</th>
                                        <td>
                                            @if($pharmacyFulfillment->form)
                                                <a href="{{ Storage::disk('public')->url($pharmacyFulfillment->form) }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="bx bx-file"></i> {{ localize('global.view_pdf') }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.created_by') }}</th>
                                        <td>{{ $pharmacyFulfillment->createdBy->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.created_at') }}</th>
                                        <td>
                                            @if($pharmacyFulfillment->created_at)
                                                {{ \Verta::instance($pharmacyFulfillment->created_at)->formatJalaliDate() }} {{ \Verta::instance($pharmacyFulfillment->created_at)->format('H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($pharmacyFulfillment->updated_at && $pharmacyFulfillment->updated_at != $pharmacyFulfillment->created_at)
                                    <tr>
                                        <th>{{ localize('global.updated_at') }}</th>
                                        <td>
                                            {{ \Verta::instance($pharmacyFulfillment->updated_at)->formatJalaliDate() }} {{ \Verta::instance($pharmacyFulfillment->updated_at)->format('H:i') }}
                                            @if($pharmacyFulfillment->updatedBy)
                                                <br><small class="text-muted">by {{ $pharmacyFulfillment->updatedBy->name }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
