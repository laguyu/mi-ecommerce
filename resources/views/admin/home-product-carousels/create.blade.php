@extends('layouts.app', ['title' => 'Crear carrusel'])

@section('content')
    <section class="card">
        <h1>Nuevo carrusel home</h1>
        @include('admin.home-product-carousels.partials.form', [
            'action' => route('admin.home-product-carousels.store'),
            'method' => 'POST',
            'carousel' => null,
            'selectedProducts' => $selectedProducts,
            'button' => 'Guardar carrusel',
        ])
    </section>
@endsection
