@extends('errors::illustrated-layout')

@section('code', '403')
@section('title', __('Forbidden'))

@section('image')
<div style="background-image: url({{ asset('/svg/403.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection

@section('message', __('په بښني سره! تاسو دغه برخی ته د ننوتلو اجازه نلرۍ' ?: __('Sorry, you are forbidden from accessing this page.')))
