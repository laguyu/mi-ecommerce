@extends('layouts.app', ['title' => 'Crear categoria'])

@section('content')
    <section class="card">
        <h1>Nueva categoria</h1>
        @include('admin.categories.partials.form', [
            'action' => route('admin.categories.store'),
            'method' => 'POST',
            'category' => null,
            'categories' => $categories,
            'button' => 'Guardar categoria',
        ])
    </section>
@endsection
