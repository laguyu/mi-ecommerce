@extends('layouts.app', ['title' => 'Mis pedidos'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap; margin-bottom:.5rem;">
            <h1 style="margin:0;">Mis pedidos</h1>
            <a class="btn btn-outline" href="{{ route('storefront.home') }}">Volver a la tienda</a>
        </div>

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

        <form method="GET" action="{{ route('account.orders.index') }}" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 1rem;">
            <label>
                Buscar
                <input class="input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Orden, correo o nombre">
            </label>

            <label>
                Estado
                <select class="select" name="status">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" @selected(($filters['status'] ?? '') === $statusValue)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                Metodo de pago
                <select class="select" name="payment_method">
                    <option value="">Todos</option>
                    @foreach($paymentMethodLabels as $paymentMethodValue => $paymentMethodLabel)
                        <option value="{{ $paymentMethodValue }}" @selected(($filters['payment_method'] ?? '') === $paymentMethodValue)>{{ $paymentMethodLabel }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                Fecha desde
                <input class="input" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </label>

            <label>
                Fecha hasta
                <input class="input" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            </label>

            <div class="actions" style="align-items: end;">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn btn-outline" href="{{ route('account.orders.export', request()->query()) }}">Exportar Excel</a>
                <a class="btn btn-outline" href="{{ route('account.orders.index') }}">Limpiar</a>
            </div>
        </form>

        @if($orders->isEmpty())
            <p>Aun no tienes pedidos registrados.</p>
        @else
            <div class="grid">
                @foreach($orders as $order)
                    <article class="card" style="border-style:dashed;">
                        <p>
                            <strong>Orden:</strong> {{ $order->order_number }}<br>
                            <strong>Estado:</strong> {{ $statusLabels[$order->status] ?? $order->status }}<br>
                            <strong>Metodo:</strong> {{ $paymentMethodLabels[$order->payment_method] ?? strtoupper((string) $order->payment_method) }}<br>
                            <strong>Total:</strong> ${{ number_format((float) $order->total, 2) }}<br>
                            <strong>Fecha:</strong> {{ optional($order->paid_at)->toDateTimeString() ?? $order->created_at->toDateTimeString() }}
                        </p>

                        <div class="actions" style="margin-bottom:.6rem;">
                            <a class="btn btn-outline" href="{{ route('account.orders.show', $order) }}">Ver pedido completo</a>
                        </div>

                        <div style="overflow-x:auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($order->items as $item)
                                        <tr>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>${{ number_format((float) $item->line_total, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">No hay productos registrados para este pedido.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top:1rem;">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
