@extends('layouts.app', ['title' => 'Editar cupon'])

@section('content')
    <section class="card">
        <h1>Editar cupon #{{ $coupon->id }}</h1>
        @include('admin.coupons.partials.form', [
            'action' => route('admin.coupons.update', $coupon),
            'method' => 'PUT',
            'coupon' => $coupon,
            'button' => 'Actualizar cupon',
        ])
    </section>
@endsection
