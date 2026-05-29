@extends('layouts.app', ['title' => 'Restablecer contraseña'])

@section('content')
    <section class="card" style="max-width: 520px; margin: 0 auto;">
        <h1>Nueva contraseña</h1>
        <p style="margin:.35rem 0 1rem; color:#64748b;">Define tu nueva contraseña para volver a entrar.</p>

        <form method="POST" action="{{ route('password.update') }}" style="display:block;">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <label style="display:block; margin-bottom:.75rem;">
                Correo
                <input class="input" type="email" name="email" value="{{ old('email', $email) }}" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label style="display:block; margin-bottom:.75rem;">
                Nueva contraseña
                <input class="input" type="password" name="password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label style="display:block; margin-bottom:.75rem;">
                Confirmar contraseña
                <input class="input" type="password" name="password_confirmation" required>
            </label>

            <div style="margin-top:.25rem;">
                <button class="btn" type="submit" style="display:block; width:100%; padding:.58rem .8rem; margin-bottom:.55rem;">Guardar contraseña</button>
                <a class="btn btn-outline" href="{{ route('login') }}" style="display:block; width:100%; padding:.58rem .8rem; text-align:center;">Volver al login</a>
            </div>
        </form>
    </section>
@endsection
