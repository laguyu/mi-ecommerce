@extends('layouts.app', ['title' => 'Detalle pedido admin'])

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

    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap; margin-bottom:1rem;">
            <div>
                <h1 style="margin:0;">Detalle pedido {{ $order->order_number }}</h1>
                <p style="margin:.25rem 0 0; color:#64748b;">Vista completa para validacion operativa y seguimiento.</p>
            </div>
            <div class="actions">
                <a class="btn" href="{{ route('admin.orders.pdf', $order) }}">Descargar PDF</a>
                <a class="btn btn-outline" href="{{ route('admin.orders.index') }}">Volver a pedidos</a>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom:1rem;">
            <article class="card" style="border-style:dashed;">
                <strong>Cliente</strong>
                <p style="margin:.35rem 0 0;">{{ $order->customer_full_name }}<br><small>{{ $order->customer_email }}</small></p>
            </article>
            <article class="card" style="border-style:dashed;">
                <strong>Usuario</strong>
                <p style="margin:.35rem 0 0;">{{ $order->user?->name ?? 'Invitado' }}</p>
            </article>
            <article class="card" style="border-style:dashed;">
                <strong>Estado</strong>
                <p style="margin:.35rem 0 0;">{{ $statusLabels[$order->status] ?? $order->status }}</p>
            </article>
            <article class="card" style="border-style:dashed;">
                <strong>Metodo</strong>
                <p style="margin:.35rem 0 0;">{{ $paymentMethodLabels[$order->payment_method] ?? strtoupper((string) $order->payment_method) }}</p>
            </article>
            <article class="card" style="border-style:dashed;">
                <strong>Total</strong>
                <p style="margin:.35rem 0 0;">${{ number_format((float) $order->total, 2) }}</p>
            </article>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        @php
                            $imageUrl = $item->product?->primaryImage?->url;
                            $imageAlt = $item->product?->primaryImage?->alt_text ?: $item->product_name;
                        @endphp
                        <tr>
                            <td>
                                @if(!empty($imageUrl))
                                    <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" style="width:56px; height:56px; border-radius:10px; object-fit:cover; border:1px solid #e2e8f0; background:#fff;">
                                @else
                                    <div style="width:56px; height:56px; border-radius:10px; border:1px dashed #cbd5e1; display:flex; align-items:center; justify-content:center; font-size:.72rem; color:#64748b;">Sin imagen</div>
                                @endif
                            </td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->sku }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>${{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No hay productos registrados para este pedido.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <article class="card" style="margin-top:1rem; border-style:dashed;">
            <h2 style="margin:0 0 .55rem; font-size:1rem;">Entrega y totales</h2>
            <p style="margin:0;">
                <strong>Direccion:</strong> {{ $order->customer_address ?: 'No aplica (pickup)' }}<br>
                <strong>Ciudad:</strong> {{ $order->customer_city ?: '-' }}<br>
                <strong>Codigo postal:</strong> {{ $order->customer_postal_code ?: '-' }}<br>
                <strong>Subtotal:</strong> ${{ number_format((float) $order->subtotal, 2) }}<br>
                <strong>Descuento:</strong> -${{ number_format((float) $order->discount_amount, 2) }}<br>
                <strong>Envio:</strong> ${{ number_format((float) $order->shipping_amount, 2) }}
            </p>
        </article>
    </section>
@endsection
