@extends('layouts.app', ['title' => 'Editar usuario'])

@section('content')
    <section class="card">
        <h1>Editar usuario</h1>
        <p style="margin-top:.35rem; color:#64748b;">Actualiza el nombre o la contraseña del usuario seleccionado.</p>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid admin-form admin-form--single" style="margin-top:1rem;">
            @csrf
            @method('PUT')

            <label>
                Nombre
                <input class="input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                Correo
                <input class="input" type="email" value="{{ $user->email }}" disabled>
            </label>

            <label>
                Rol actual
                <input class="input" type="text" value="{{ $user->role }}" disabled>
            </label>

            <label>
                Nueva contraseña
                <input class="input" type="password" name="password" placeholder="Dejar vacio para no cambiar">
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </label>

            <label>
                Confirmar nueva contraseña
                <input class="input" type="password" name="password_confirmation">
            </label>

            <div class="actions">
                <button class="btn" type="submit">Guardar cambios</button>
                <a class="btn btn-outline" href="{{ route('admin.users.index') }}">Volver</a>
            </div>
        </form>
    </section>
@endsection
