@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <section class="card" style="max-width: 480px; margin: 0 auto;">
        <h1>Iniciar sesion</h1>

        <form method="POST" action="{{ route('login.store') }}" class="grid">
            @csrf

            <label>
                Correo
                <input class="input" type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                Contrasena
                <input class="input" type="password" name="password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                <input type="checkbox" name="remember" value="1"> Recordarme
            </label>

            <a href="{{ route('password.request') }}" style="font-size:.9rem; color:#475569; text-decoration:underline;">
                ¿Olvidaste tu contraseña?
            </a>

            <button class="btn" type="submit">Entrar</button>
        </form>
    </section>
@endsection
