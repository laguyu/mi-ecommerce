@extends('layouts.app', ['title' => 'Banners secundarios home'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <h1>Panel admin - Banners secundarios</h1>
            <a class="btn" href="{{ route('admin.home-secondary-banners.create') }}">Nuevo banner secundario</a>
        </div>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titulo</th>
                        <th>Imagen</th>
                        <th>Estado</th>
                        <th>Orden</th>
                        <th>Producto vinculado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                        <tr>
                            <td>{{ $banner->id }}</td>
                            <td>
                                {{ $banner->title }}<br>
                                <small>{{ $banner->subtitle }}</small>
                            </td>
                            <td>
                                @if($banner->image_url)
                                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" style="width: 120px; height: 64px; object-fit: cover; border:1px solid #e5e7eb; border-radius:8px;">
                                @else
                                    <small>Sin imagen</small>
                                @endif
                            </td>
                            <td>{{ $banner->is_active ? 'Activo' : 'Inactivo' }}</td>
                            <td>{{ $banner->sort_order }}</td>
                            <td>{{ $banner->product?->name ?? 'Sin vincular' }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline" href="{{ route('admin.home-secondary-banners.edit', $banner) }}">Editar</a>
                                    <form method="POST" action="{{ route('admin.home-secondary-banners.destroy', $banner) }}" onsubmit="return confirm('Eliminar banner secundario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No hay banners secundarios configurados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $banners->links() }}</div>
    </section>
@endsection
