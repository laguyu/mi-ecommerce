@extends('layouts.app', ['title' => 'Editar banner home'])

@section('content')
    <section class="card">
        <h1>Editar banner #{{ $banner->id }}</h1>
        @include('admin.home-banners.partials.form', [
            'action' => route('admin.home-banners.update', $banner),
            'method' => 'PUT',
            'banner' => $banner,
            'button' => 'Actualizar banner',
        ])
    </section>
@endsection
