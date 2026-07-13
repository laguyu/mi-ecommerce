@extends('layouts.app', ['title' => 'Contacto'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <div>
                <h1>Panel admin - Contacto</h1>
                <p style="margin:.25rem 0 0; color:#64748b;">Mensajes enviados desde el formulario publico.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="grid" style="grid-template-columns: 1fr 220px auto auto; margin-top: .9rem;">
            <input class="input" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar por nombre, email o asunto">
            <select class="select" name="status">
                <option value="">Todos los estados</option>
                <option value="stored" @selected(($status ?? '') === 'stored')>Guardado</option>
                <option value="sent" @selected(($status ?? '') === 'sent')>Enviado</option>
                <option value="failed" @selected(($status ?? '') === 'failed')>Error</option>
            </select>
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-outline" href="{{ route('admin.contact-messages.index') }}">Limpiar</a>
        </form>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contactMessages as $contactMessage)
                        <tr>
                            <td>{{ $contactMessage->id }}</td>
                            <td>{{ $contactMessage->name }}</td>
                            <td>{{ $contactMessage->email }}</td>
                            <td>{{ $contactMessage->subject }}</td>
                            <td>{{ $contactMessage->status }}</td>
                            <td>{{ optional($contactMessage->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline" href="{{ route('admin.contact-messages.show', $contactMessage) }}">Ver</a>
                                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}" onsubmit="return confirm('Eliminar mensaje?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No hay mensajes de contacto.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $contactMessages->links() }}</div>
    </section>
@endsection