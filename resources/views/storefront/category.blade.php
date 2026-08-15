@extends('layouts.storefront')

@section('title', $category->name)

@section('content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">{{ $category->name }}</h1>

    @include('storefront.partials.product-grid')
@endsection
