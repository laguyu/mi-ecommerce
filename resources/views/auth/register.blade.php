@extends('layouts.app', ['title' => 'Registro'])

@section('content')
    <section class="card" style="max-width: 480px; margin: 0 auto;">
        <h1>Crear cuenta</h1>

        <form method="POST" action="{{ route('register.store') }}" class="grid">
            @csrf

            <label>
                Nombre
                <input class="input" type="text" name="name" value="{{ old('name') }}" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </label>

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
                Confirmar contrasena
                <input class="input" type="password" name="password_confirmation" required>
            </label>

            <button class="btn" type="submit">Crear cuenta</button>
        </form>
    </section>
@endsection
