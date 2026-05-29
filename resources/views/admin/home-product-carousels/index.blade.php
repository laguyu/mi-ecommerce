@extends('layouts.app', ['title' => 'Carruseles home'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <h1>Panel admin - Carruseles home</h1>
            <a class="btn" href="{{ route('admin.home-product-carousels.create') }}">Nuevo carrusel</a>
        </div>

        <p style="margin-top:.5rem; color:#64748b;">Puedes crear hasta 3 módulos de carrusel para el home.</p>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titulo</th>
                        <th>Imagen</th>
                        <th>Estado</th>
                        <th>Orden</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carousels as $carousel)
                        <tr>
                            <td>{{ $carousel->id }}</td>
                            <td>
                                {{ $carousel->title }}<br>
                                <small>{{ $carousel->subtitle }}</small>
                            </td>
                            <td>
                                @if($carousel->image_url)
                                    <img src="{{ $carousel->image_url }}" alt="{{ $carousel->title }}" style="width: 220px; max-width: 100%; height: 110px; object-fit: cover; border:1px solid #e5e7eb; border-radius:10px; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);">
                                @else
                                    <small>Sin imagen</small>
                                @endif
                            </td>
                            <td>{{ $carousel->is_active ? 'Activo' : 'Inactivo' }}</td>
                            <td>{{ $carousel->sort_order }}</td>
                            <td>{{ $carousel->products_count }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline" href="{{ route('admin.home-product-carousels.edit', $carousel) }}">Editar</a>
                                    <form method="POST" action="{{ route('admin.home-product-carousels.destroy', $carousel) }}" onsubmit="return confirm('Eliminar carrusel?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No hay carruseles configurados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $carousels->links() }}</div>
    </section>
@endsection
