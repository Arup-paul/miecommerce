@extends('layouts.storefront')

@section('title', 'Shop')

@section('content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">All Products</h1>

    @include('storefront.partials.product-grid')
@endsection
