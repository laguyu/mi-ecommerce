@extends('layouts.app', ['title' => 'Crear banner secundario'])

@section('content')
    <section class="card">
        <h1>Nuevo banner secundario</h1>
        @include('admin.home-secondary-banners.partials.form', [
            'action' => route('admin.home-secondary-banners.store'),
            'method' => 'POST',
            'banner' => null,
            'button' => 'Guardar banner secundario',
        ])
    </section>
@endsection
