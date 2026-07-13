@extends('layouts.app', ['title' => 'Detalle contacto'])

@section('content')
    @php
        $statusLabels = [
            'stored' => 'Guardado',
            'sent' => 'Enviado',
            'failed' => 'Error',
        ];
    @endphp

    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap; margin-bottom:1rem;">
            <div>
                <h1 style="margin:0;">Mensaje de {{ $contactMessage->name }}</h1>
                <p style="margin:.25rem 0 0; color:#64748b;">{{ $statusLabels[$contactMessage->status] ?? $contactMessage->status }}</p>
            </div>
            <div class="actions">
                <a class="btn btn-outline" href="{{ route('admin.contact-messages.index') }}">Volver</a>
                <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}" onsubmit="return confirm('Eliminar mensaje?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline" type="submit">Eliminar</button>
                </form>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom:1rem;">
            <article class="card" style="border-style:dashed;">
                <strong>Correo</strong>
                <p style="margin:.35rem 0 0;">{{ $contactMessage->email }}</p>
            </article>
            <article class="card" style="border-style:dashed;">
                <strong>Teléfono</strong>
                <p style="margin:.35rem 0 0;">{{ $contactMessage->phone ?: '-' }}</p>
            </article>
            <article class="card" style="border-style:dashed;">
                <strong>Destino</strong>
                <p style="margin:.35rem 0 0;">{{ $contactMessage->recipient_email ?: '-' }}</p>
            </article>
            <article class="card" style="border-style:dashed;">
                <strong>Fecha</strong>
                <p style="margin:.35rem 0 0;">{{ optional($contactMessage->created_at)->format('Y-m-d H:i') }}</p>
            </article>
        </div>

        <div class="card" style="border-style:dashed; margin-bottom:1rem;">
            <strong>Asunto</strong>
            <p style="margin:.35rem 0 0;">{{ $contactMessage->subject }}</p>
        </div>

        <div class="card" style="border-style:dashed; white-space:pre-wrap;">
            {{ $contactMessage->message }}
        </div>

        @if($contactMessage->delivery_error)
            <div class="card" style="border-style:dashed; margin-top:1rem; border-color:#fecaca; background:#fef2f2; color:#991b1b; white-space:pre-wrap;">
                <strong>Error de envio</strong>
                <div style="margin-top:.35rem;">{{ $contactMessage->delivery_error }}</div>
            </div>
        @endif
    </section>
@endsection