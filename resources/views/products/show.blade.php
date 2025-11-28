@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>{{ $product->name }}</h1>
        <img src="{{ $product->image }}" width="200">
        <p>{{ $product->description }}</p>
        <p><strong>{{ number_format($product->price, 0) }} ៛</strong></p>
        <form action="{{ route('checkout', $product->id) }}" method="POST">
            @csrf
            <button class="btn btn-success">Generate KHQR to Pay</button>
        </form>
    </div>
@endsection
