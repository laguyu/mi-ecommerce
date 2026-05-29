@extends('layouts.app', ['title' => 'Configuracion del sitio'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <div>
                <h1>Configuracion del sitio</h1>
                <p style="margin:.25rem 0 0; color:#64748b;">Edita el nombre, logo, footer y datos de contacto del ecommerce.</p>
            </div>
        </div>

        @include('admin.site-settings.partials.form', [
            'action' => route('admin.site-settings.update'),
            'method' => 'PUT',
            'siteSetting' => $siteSetting,
            'button' => 'Guardar configuracion',
        ])
    </section>
@endsection
