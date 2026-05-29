@extends('layouts.app', ['title' => 'Admin promociones'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <h1>Panel admin - Promociones</h1>
            <a class="btn" href="{{ route('admin.promotions.create') }}">Nueva promocion</a>
        </div>

        <form method="GET" action="{{ route('admin.promotions.index') }}" class="grid" style="grid-template-columns: 1fr auto auto; margin-top: .9rem;">
            <input class="input" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar por nombre de promocion">
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-outline" href="{{ route('admin.promotions.index') }}">Limpiar</a>
        </form>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Promocion</th>
                        <th>Descuento</th>
                        <th>Estado</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Cronometro</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promotion)
                        <tr>
                            <td>{{ $promotion->id }}</td>
                            <td>{{ $promotion->name }}</td>
                            <td>{{ $promotion->discount_percentage }}%</td>
                            <td>{{ $promotion->status === 'active' ? 'Activa' : 'Inactiva' }}</td>
                            <td>{{ optional($promotion->starts_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($promotion->ends_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <span
                                    class="promotion-timer"
                                    data-starts-at="{{ optional($promotion->starts_at)->toIso8601String() }}"
                                    data-ends-at="{{ optional($promotion->ends_at)->toIso8601String() }}"
                                                        style="display:inline-flex; align-items:center; justify-content:center; min-width:92px; padding:.3rem .65rem; border-radius:999px; background:#0f172a; color:#fef08a; font-weight:700; font-size:.95rem; letter-spacing:.02em;"
                                >
                                    --:--:--
                                </span>
                            </td>
                            <td>{{ $promotion->products_count }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline" href="{{ route('admin.promotions.edit', $promotion) }}">Editar</a>
                                    @if(auth()->user()->hasPermission('delete_products'))
                                        <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" onsubmit="return confirm('Eliminar promocion?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline" type="submit">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No hay promociones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $promotions->links() }}</div>
    </section>

    <script>
        (function () {
            const timers = Array.from(document.querySelectorAll('.promotion-timer'));

            if (timers.length === 0) {
                return;
            }

            function formatDuration(ms) {
                const totalSeconds = Math.max(0, Math.floor(ms / 1000));
                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.floor((totalSeconds % 86400) / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                const pad = (value) => String(value).padStart(2, '0');

                if (days > 0) {
                    return `${days}d ${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                }

                return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
            }

            function updateTimer(el) {
                const startsAt = el.dataset.startsAt ? new Date(el.dataset.startsAt) : null;
                const endsAt = el.dataset.endsAt ? new Date(el.dataset.endsAt) : null;
                const now = new Date();
                const target = endsAt && !Number.isNaN(endsAt.getTime()) ? endsAt : startsAt;

                if (!target || Number.isNaN(target.getTime())) {
                    el.textContent = '-';
                    return;
                }

                const diff = target.getTime() - now.getTime();
                el.textContent = diff > 0 ? formatDuration(diff) : '00:00:00';
            }

            function tick() {
                timers.forEach(updateTimer);
            }

            tick();
            setInterval(tick, 1000);
        })();
    </script>
@endsection
