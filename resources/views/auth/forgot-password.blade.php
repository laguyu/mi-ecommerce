@extends('layouts.app', ['title' => 'Recuperar contraseña'])

@section('content')
    <section class="card" style="max-width: 520px; margin: 0 auto;">
        <h1>Recuperar contraseña</h1>
        <p style="margin:.35rem 0 1rem; color:#64748b;">Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>

        <form method="POST" action="{{ route('password.email') }}" style="display:block;">
            @csrf

            <label style="display:block; margin-bottom:.75rem;">
                Correo
                <input class="input" type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </label>

            <div style="margin-top:.25rem;">
                <button class="btn" type="submit" style="display:block; width:100%; padding:.58rem .8rem; margin-bottom:.55rem;">Enviar enlace</button>
                <a class="btn btn-outline" href="{{ route('login') }}" style="display:block; width:100%; padding:.58rem .8rem; text-align:center;">Volver al login</a>
            </div>
        </form>
    </section>
@endsection
