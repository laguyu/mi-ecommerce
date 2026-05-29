@extends('layouts.app', ['title' => 'Crear promocion'])

@section('content')
    <section class="card">
        <h1>Nueva promocion</h1>
        @include('admin.promotions.partials.form', [
            'action' => route('admin.promotions.store'),
            'method' => 'POST',
            'promotion' => null,
            'selectedProducts' => $selectedProducts,
            'button' => 'Guardar promocion',
        ])
    </section>
@endsection
