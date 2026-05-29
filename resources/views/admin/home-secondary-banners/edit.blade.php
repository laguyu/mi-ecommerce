@extends('layouts.app', ['title' => 'Editar banner secundario'])

@section('content')
    <section class="card">
        <h1>Editar banner secundario #{{ $banner->id }}</h1>
        @include('admin.home-secondary-banners.partials.form', [
            'action' => route('admin.home-secondary-banners.update', $banner),
            'method' => 'PUT',
            'banner' => $banner,
            'button' => 'Actualizar banner secundario',
        ])
    </section>
@endsection
