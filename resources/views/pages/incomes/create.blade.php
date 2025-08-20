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
                        <h5 class="mb-0">{{ localize('global.create_income_record') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('incomes.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="medicine_id">{{ localize('global.medicine') }}</label>
                                        <select class="form-control select2 @error('medicine_id') is-invalid @enderror"
                                            id="medicine_id" name="medicine_id" required>
                                            <option value="">{{ localize('global.select_medicine') }}</option>
                                            @foreach ($medicines as $medicine)
                                                <option value="{{ $medicine->id }}"
                                                    {{ old('medicine_id') == $medicine->id ? 'selected' : '' }}>
                                                    {{ $medicine->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('medicine_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="income_type">{{ localize('global.income_type') }}</label>
                                        <select class="form-control @error('income_type') is-invalid @enderror"
                                            id="income_type" name="income_type" required>
                                            <option value="">{{ localize('global.select_income_type') }}</option>
                                            @foreach ($incomeTypes as $type)
                                                <option value="{{ $type }}"
                                                    {{ old('income_type') == $type ? 'selected' : '' }}>
                                                    {{ localize('global.' . $type) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('income_type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="amount">{{ localize('global.amount') }}</label>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                            id="amount" name="amount" value="{{ old('amount') }}" min="1" required>
                                        @error('amount')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="batch_number">{{ localize('global.batch_number') }}</label>
                                        <input type="text" class="form-control @error('batch_number') is-invalid @enderror"
                                            id="batch_number" name="batch_number" value="{{ old('batch_number') }}">
                                        @error('batch_number')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="expiry_date">{{ localize('global.expiry_date') }}</label>
                                        <input type="date" class="form-control @error('expiry_date') is-invalid @enderror"
                                            id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                                        @error('expiry_date')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="supplier_name">{{ localize('global.supplier_name') }}</label>
                                        <input type="text" class="form-control @error('supplier_name') is-invalid @enderror"
                                            id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}">
                                        @error('supplier_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="supplier_contact">{{ localize('global.supplier_contact') }}</label>
                                        <input type="text" class="form-control @error('supplier_contact') is-invalid @enderror"
                                            id="supplier_contact" name="supplier_contact" value="{{ old('supplier_contact') }}">
                                        @error('supplier_contact')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="purchase_price">{{ localize('global.purchase_price') }}</label>
                                        <input type="number" step="0.01" class="form-control @error('purchase_price') is-invalid @enderror"
                                            id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" min="0" required>
                                        @error('purchase_price')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="purchase_date">{{ localize('global.purchase_date') }}</label>
                                        <input type="date" class="form-control @error('purchase_date') is-invalid @enderror"
                                            id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}">
                                        @error('purchase_date')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="invoice_number">{{ localize('global.invoice_number') }}</label>
                                        <input type="text" class="form-control @error('invoice_number') is-invalid @enderror"
                                            id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}">
                                        @error('invoice_number')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="notes">{{ localize('global.notes') }}</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                            id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            {{ localize('global.create') }}
                                        </button>
                                        <a href="{{ route('incomes.index') }}" class="btn btn-secondary">
                                            {{ localize('global.cancel') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
