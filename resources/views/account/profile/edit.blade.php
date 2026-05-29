@extends('layouts.app', ['title' => 'Mi perfil'])

@section('content')
    @php
        $statusLabels = [
            'pending_payment' => 'Pendiente de pago',
            'paid' => 'Pagado',
            'payment_failed' => 'Pago fallido',
        ];

        $paymentMethodLabels = [
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'transferencia' => 'Transferencia',
            'efectivo' => 'Efectivo',
        ];
    @endphp

    <style>
        .profile-form {
            max-width: 940px;
            margin: 1rem auto 0;
            display: grid;
            grid-template-columns: repeat(2, minmax(260px, 1fr));
            gap: 0.8rem 1rem;
        }

        .profile-form .full-row {
            grid-column: 1 / -1;
        }

        .profile-actions {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.55rem;
            margin-top: 0.25rem;
        }

        .profile-actions .btn,
        .profile-actions .btn-outline {
            padding: 0.58rem 0.8rem;
            font-size: 0.9rem;
            text-align: center;
            width: 100%;
        }

        @media (max-width: 820px) {
            .profile-form {
                grid-template-columns: 1fr;
            }

            .profile-form .full-row {
                grid-column: auto;
            }
        }
    </style>

    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:.7rem; flex-wrap:wrap;">
            <div>
                <h1>Mi perfil</h1>
                <p style="margin-top:.35rem; color:#64748b;">Actualiza tu nombre y cambia tu contraseña desde la tienda.</p>
            </div>
            <a class="btn btn-outline" href="{{ route('storefront.home') }}">Volver a la tienda</a>
        </div>

        <form method="POST" action="{{ route('account.profile.update') }}" class="profile-form">
            @csrf
            @method('PUT')

            <label>
                Nombre
                <input class="input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                Correo
                <input class="input" type="email" value="{{ $user->email }}" disabled>
            </label>

            <label class="full-row">
                Contraseña actual
                <input class="input" type="password" name="current_password" placeholder="Solo si vas a cambiar la contraseña">
                @error('current_password') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label class="full-row">
                Nueva contraseña
                <input class="input" type="password" name="password" placeholder="Dejar vacio para no cambiar">
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label class="full-row">
                Confirmar nueva contraseña
                <input class="input" type="password" name="password_confirmation">
            </label>

            <div class="profile-actions">
                <button class="btn" type="submit">Guardar cambios</button>
                <a class="btn btn-outline" href="{{ route('account.orders.index') }}">Ver todos mis pedidos</a>
            </div>
        </form>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h2 style="margin:0 0 .75rem;">Pedidos recientes</h2>

        @if(($recentOrders ?? collect())->isEmpty())
            <p style="margin:0; color:#64748b;">Aun no tienes pedidos realizados.</p>
        @else
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th>Metodo</th>
                            <th>Total</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $statusLabels[$order->status] ?? $order->status }}</td>
                                <td>{{ $paymentMethodLabels[$order->payment_method] ?? strtoupper((string) $order->payment_method) }}</td>
                                <td>${{ number_format((float) $order->total, 2) }}</td>
                                <td>{{ $order->created_at->toDateTimeString() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
