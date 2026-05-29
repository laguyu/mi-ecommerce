@extends('layouts.app', ['title' => 'Admin pedidos'])

@section('content')
    <section class="card">
        <h1>Panel admin - Pedidos</h1>

        <style>
            .orders-status-form {
                display: flex;
                align-items: center;
                gap: .45rem;
                flex-wrap: wrap;
                min-width: 260px;
            }

            .orders-status-form .select {
                min-width: 170px;
            }

            .orders-status-form .btn {
                padding: .45rem .7rem;
                border-radius: 999px;
                white-space: nowrap;
            }

            .order-status-badge {
                display: inline-flex;
                align-items: center;
                padding: .22rem .55rem;
                border-radius: 999px;
                font-size: .72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .04em;
                margin-bottom: .35rem;
            }

            .order-status-badge--pending_payment {
                background: #fef3c7;
                color: #92400e;
            }

            .order-status-badge--paid {
                background: #dcfce7;
                color: #166534;
            }

            .order-status-badge--payment_failed {
                background: #fee2e2;
                color: #991b1b;
            }

            .orders-edit-button {
                padding: .42rem .72rem;
                border-radius: 999px;
                white-space: nowrap;
            }

            .order-status-modal::backdrop {
                background: rgba(15, 23, 42, 0.45);
            }

            .order-status-modal {
                border: 0;
                border-radius: 16px;
                padding: 0;
                width: min(100%, 520px);
                box-shadow: 0 24px 64px rgba(15, 23, 42, 0.25);
            }

            .order-status-modal__content {
                display: grid;
                gap: 1rem;
                padding: 1rem;
                background: #fff;
            }

            .order-status-modal__header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: .75rem;
            }

            .order-status-modal__header h3 {
                margin: 0;
            }

            .order-status-modal__header p {
                margin: .2rem 0 0;
                color: #64748b;
            }

            .order-status-modal__close {
                border: 1px solid #e5e7eb;
                background: #fff;
                color: #334155;
                border-radius: 999px;
                width: 34px;
                height: 34px;
                line-height: 1;
                font-size: 1.05rem;
            }

            .order-status-modal__form {
                display: grid;
                gap: .8rem;
            }

            .order-status-modal__actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                flex-wrap: wrap;
            }

            .orders-products-list {
                display: grid;
                gap: .2rem;
                min-width: 210px;
            }

            .orders-products-item {
                font-size: .82rem;
                line-height: 1.35;
                color: #334155;
            }

            .orders-products-empty {
                font-size: .82rem;
                color: #94a3b8;
            }
        </style>

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

        @if(session('status'))
            <div class="alert" style="margin-bottom:1rem;">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 1rem;">
            <label>
                Buscar
                <input class="input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Orden, correo o nombre">
            </label>

            <label>
                Estado
                <select class="select" name="status">
                    <option value="">Todos</option>
                    <option value="pending_payment" @selected(($filters['status'] ?? '') === 'pending_payment')>Pendiente de pago</option>
                    <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Pagado</option>
                    <option value="payment_failed" @selected(($filters['status'] ?? '') === 'payment_failed')>Pago fallido</option>
                </select>
            </label>

            <label>
                Metodo
                <select class="select" name="payment_method">
                    <option value="">Todos</option>
                    <option value="stripe" @selected(($filters['payment_method'] ?? '') === 'stripe')>Stripe</option>
                    <option value="paypal" @selected(($filters['payment_method'] ?? '') === 'paypal')>PayPal</option>
                    <option value="transferencia" @selected(($filters['payment_method'] ?? '') === 'transferencia')>Transferencia</option>
                    <option value="efectivo" @selected(($filters['payment_method'] ?? '') === 'efectivo')>Efectivo</option>
                </select>
            </label>

            <div class="actions" style="align-items:end;">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn btn-outline" href="{{ route('admin.orders.export', request()->query()) }}">Exportar Excel</a>
                <a class="btn btn-outline" href="{{ route('admin.orders.index') }}">Limpiar</a>
            </div>
        </form>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Cliente</th>
                        <th>Usuario</th>
                        <th>Productos comprados</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Metodo</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer_full_name }}<br><small>{{ $order->customer_email }}</small></td>
                            <td>{{ $order->user?->name ?? 'Invitado' }}</td>
                            <td>
                                @if($order->items->isEmpty())
                                    <span class="orders-products-empty">Sin productos cargados</span>
                                @else
                                    <div class="orders-products-list">
                                        @foreach($order->items as $item)
                                            <div class="orders-products-item">
                                                {{ $item->product_name }} x {{ $item->quantity }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>${{ number_format((float)$order->total, 2) }}</td>
                            <td>
                                <span class="order-status-badge order-status-badge--{{ $order->status }}">
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                </span>
                                <button
                                    type="button"
                                    class="btn btn-outline orders-edit-button"
                                    data-order-number="{{ $order->order_number }}"
                                    data-status="{{ $order->status }}"
                                    data-action="{{ route('admin.orders.update-status', $order) }}"
                                    data-current-status-label="{{ $statusLabels[$order->status] ?? $order->status }}"
                                >
                                    Editar
                                </button>
                            </td>
                            <td>{{ $paymentMethodLabels[$order->payment_method] ?? strtoupper((string)$order->payment_method) }}</td>
                            <td>{{ $order->created_at->toDateTimeString() }}</td>
                            <td>
                                <a class="btn btn-outline" href="{{ route('admin.orders.show', $order) }}">Ver detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No hay pedidos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $orders->links() }}</div>
    </section>

    <dialog id="orderStatusModal" class="order-status-modal">
        <form method="POST" id="orderStatusModalForm" class="order-status-modal__content">
            @csrf
            @method('PATCH')

            <div class="order-status-modal__header">
                <div>
                    <h3>Editar estado</h3>
                    <p id="orderStatusModalDescription">Actualiza el estado del pedido seleccionado.</p>
                </div>
                <button type="button" class="order-status-modal__close" id="orderStatusModalClose" aria-label="Cerrar">&times;</button>
            </div>

            <label>
                Estado
                <select class="select" name="status" id="orderStatusModalSelect">
                    @foreach($statusLabels as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </label>

            <div class="order-status-modal__actions">
                <button type="button" class="btn btn-outline" id="orderStatusModalCancel">Cancelar</button>
                <button type="submit" class="btn">Guardar cambios</button>
            </div>
        </form>
    </dialog>

    <script>
        (function () {
            const modal = document.getElementById('orderStatusModal');
            const form = document.getElementById('orderStatusModalForm');
            const select = document.getElementById('orderStatusModalSelect');
            const description = document.getElementById('orderStatusModalDescription');
            const closeButton = document.getElementById('orderStatusModalClose');
            const cancelButton = document.getElementById('orderStatusModalCancel');
            const editButtons = document.querySelectorAll('[data-action][data-order-number]');

            if (!modal || !form || !select || !description) {
                return;
            }

            function closeModal() {
                if (typeof modal.close === 'function') {
                    modal.close();
                }
            }

            editButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    form.action = button.dataset.action || form.action;
                    select.value = button.dataset.status || 'pending_payment';
                    description.textContent = `Pedido ${button.dataset.orderNumber || ''} - Estado actual: ${button.dataset.currentStatusLabel || ''}`;

                    if (typeof modal.showModal === 'function') {
                        modal.showModal();
                    }
                });
            });

            [closeButton, cancelButton].forEach((button) => {
                if (!button) return;
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        })();
    </script>
@endsection
