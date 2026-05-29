@extends('layouts.app', ['title' => 'Editar promocion'])

@section('content')
    <section class="card">
        <h1>Editar promocion #{{ $promotion->id }}</h1>
        @include('admin.promotions.partials.form', [
            'action' => route('admin.promotions.update', $promotion),
            'method' => 'PUT',
            'promotion' => $promotion,
            'selectedProducts' => $selectedProducts,
            'button' => 'Actualizar promocion',
        ])
    </section>
@endsection
