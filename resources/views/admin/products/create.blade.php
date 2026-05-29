@extends('layouts.app', ['title' => 'Crear producto'])

@section('content')
    <section class="card">
        <h1>Nuevo producto</h1>
        @include('admin.products.partials.form', [
            'action' => route('admin.products.store'),
            'method' => 'POST',
            'product' => null,
            'brands' => $brands,
            'button' => 'Guardar producto',
        ])
    </section>
@endsection
