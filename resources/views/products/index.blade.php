@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Product List</h1>
        <div class="row">
            @foreach ($products as $product)
                <div class="col-md-4">
                    <div class="card mb-3">
                        <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}">
                        <div class="card-body">
                            <h5>{{ $product->name }}</h5>
                            <p>{{ $product->description }}</p>
                            <p><strong>{{ number_format($product->price, 0) }} ៛</strong></p>
                            <a href="{{ route('product.show', $product->id) }}" class="btn
btn-primary">Buy</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
