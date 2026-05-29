@extends('layouts.app', ['title' => 'Editar producto'])

@section('content')
    <section class="card">
        <h1>Editar producto #{{ $product->id }}</h1>
        @include('admin.products.partials.form', [
            'action' => route('admin.products.update', $product),
            'method' => 'PUT',
            'product' => $product,
            'brands' => $brands,
            'button' => 'Actualizar producto',
        ])
    </section>
@endsection
