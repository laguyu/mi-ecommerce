@extends('layouts.app', ['title' => 'Newsletter'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <h1>Panel admin - Newsletter</h1>
            <div class="actions">
                <a class="btn btn-outline" href="{{ route('admin.newsletter-subscribers.export', ['q' => $search ?? null, 'status' => 'all']) }}">Excel todos</a>
                <a class="btn btn-outline" href="{{ route('admin.newsletter-subscribers.export', ['q' => $search ?? null, 'status' => 'active']) }}">Excel activos</a>
                <a class="btn btn-outline" href="{{ route('admin.newsletter-subscribers.export', ['q' => $search ?? null, 'status' => 'inactive']) }}">Excel inactivos</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.newsletter-subscribers.index') }}" class="grid" style="grid-template-columns: 1fr auto auto; margin-top: .9rem;">
            <input class="input" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar por email">
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-outline" href="{{ route('admin.newsletter-subscribers.index') }}">Limpiar</a>
        </form>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Suscrito desde</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber->id }}</td>
                            <td>{{ $subscriber->email }}</td>
                            <td>{{ $subscriber->is_active ? 'Activo' : 'Inactivo' }}</td>
                            <td>{{ optional($subscriber->subscribed_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="{{ route('admin.newsletter-subscribers.toggle', $subscriber) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline" type="submit">{{ $subscriber->is_active ? 'Desactivar' : 'Activar' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.newsletter-subscribers.destroy', $subscriber) }}" onsubmit="return confirm('Eliminar suscriptor?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No hay suscriptores.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $subscribers->links() }}</div>
    </section>
@endsection
