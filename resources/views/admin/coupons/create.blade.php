@extends('layouts.app', ['title' => 'Crear cupon'])

@section('content')
    <section class="card">
        <h1>Nuevo cupon</h1>
        @include('admin.coupons.partials.form', [
            'action' => route('admin.coupons.store'),
            'method' => 'POST',
            'coupon' => null,
            'button' => 'Guardar cupon',
        ])
    </section>
@endsection
