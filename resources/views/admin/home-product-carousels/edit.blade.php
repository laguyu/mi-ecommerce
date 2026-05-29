@extends('layouts.app', ['title' => 'Editar carrusel'])

@section('content')
    <section class="card">
        <h1>Editar carrusel #{{ $carousel->id }}</h1>
        @include('admin.home-product-carousels.partials.form', [
            'action' => route('admin.home-product-carousels.update', $carousel),
            'method' => 'PUT',
            'carousel' => $carousel,
            'selectedProducts' => $selectedProducts,
            'button' => 'Actualizar carrusel',
        ])
    </section>
@endsection
