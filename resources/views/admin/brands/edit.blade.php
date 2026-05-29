@extends('layouts.app', ['title' => 'Editar marca'])

@section('content')
    <section class="card">
        <h1>Editar marca #{{ $brand->id }}</h1>
        @include('admin.brands.partials.form', [
            'action' => route('admin.brands.update', $brand),
            'method' => 'PUT',
            'brand' => $brand,
            'button' => 'Actualizar marca',
        ])
    </section>
@endsection
