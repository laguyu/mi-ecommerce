@extends('layouts.app', ['title' => 'Editar categoria'])

@section('content')
    <section class="card">
        <h1>Editar categoria #{{ $category->id }}</h1>
        @include('admin.categories.partials.form', [
            'action' => route('admin.categories.update', $category),
            'method' => 'PUT',
            'category' => $category,
            'categories' => $categories,
            'button' => 'Actualizar categoria',
        ])
    </section>
@endsection
