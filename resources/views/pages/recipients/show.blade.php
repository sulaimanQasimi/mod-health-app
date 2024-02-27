@extends('layouts.master')

@section('content')
    <div class="container-xxl container-p-y">
        <div class="col-xl-12 col-md-8 col-12 mb-md-0 mb-4">
            <div class="card invoice-preview-card">
                <div class="card-body">
                    <p>Title: {{ $recipient->name_dr }}</p>
                    <p>Description: {{ $recipient->description }}</p>
                    <p>Expires at: {{ $recipient->type }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
