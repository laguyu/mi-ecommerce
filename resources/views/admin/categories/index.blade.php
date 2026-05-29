@extends('layouts.app', ['title' => 'Admin categorias'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <h1>Panel admin - Categorias</h1>
            <a class="btn" href="{{ route('admin.categories.create') }}">Nueva categoria</a>
        </div>

        <form method="GET" action="{{ route('admin.categories.index') }}" class="grid" style="grid-template-columns: 1fr auto auto; margin-top: .9rem;">
            <input class="input" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar por nombre o slug">
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-outline" href="{{ route('admin.categories.index') }}">Limpiar</a>
        </form>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Padre</th>
                        <th>Slug</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->full_name }}</td>
                            <td>{{ $category->parent?->name ?? '-' }}</td>
                            <td>{{ $category->slug }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline" href="{{ route('admin.categories.edit', $category) }}">Editar</a>
                                    @if(auth()->user()->hasPermission('delete_categories'))
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Eliminar categoria?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline" type="submit">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay categorias.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $categories->links() }}</div>
    </section>
@endsection
