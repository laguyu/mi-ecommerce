@extends('layouts.app', ['title' => 'Admin marcas'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <h1>Panel admin - Marcas</h1>
            <a class="btn" href="{{ route('admin.brands.create') }}">Nueva marca</a>
        </div>

        <form method="GET" action="{{ route('admin.brands.index') }}" class="grid" style="grid-template-columns: 1fr auto auto; margin-top: .9rem;">
            <input class="input" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar por nombre o slug">
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-outline" href="{{ route('admin.brands.index') }}">Limpiar</a>
        </form>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td>{{ $brand->id }}</td>
                            <td>{{ $brand->name }}</td>
                            <td>{{ $brand->slug }}</td>
                            <td>{{ $brand->products_count }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline" href="{{ route('admin.brands.edit', $brand) }}">Editar</a>
                                    @if(auth()->user()->hasPermission('delete_products'))
                                        <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Eliminar marca?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline" type="submit">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No hay marcas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $brands->links() }}</div>
    </section>
@endsection
