@extends('layouts.app', ['title' => 'Crear banner home'])

@section('content')
    <section class="card">
        <h1>Nuevo banner principal</h1>
        @include('admin.home-banners.partials.form', [
            'action' => route('admin.home-banners.store'),
            'method' => 'POST',
            'banner' => null,
            'button' => 'Guardar banner',
        ])
    </section>
@endsection
