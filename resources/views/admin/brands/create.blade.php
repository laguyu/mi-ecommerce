@extends('layouts.app', ['title' => 'Crear marca'])

@section('content')
    <section class="card">
        <h1>Nueva marca</h1>
        @include('admin.brands.partials.form', [
            'action' => route('admin.brands.store'),
            'method' => 'POST',
            'brand' => null,
            'button' => 'Guardar marca',
        ])
    </section>
@endsection
