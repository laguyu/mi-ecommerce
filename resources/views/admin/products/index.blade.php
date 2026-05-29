@extends('layouts.app', ['title' => 'Admin productos'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <h1>Panel admin - Productos</h1>
            <a class="btn" href="{{ route('admin.products.create') }}">Nuevo producto</a>
        </div>

        <form method="GET" action="{{ route('admin.products.index') }}" class="grid" style="grid-template-columns: 1fr auto auto; margin-top: .9rem;">
            <input class="input" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar por nombre, marca, SKU o slug">
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-outline" href="{{ route('admin.products.index') }}">Limpiar</a>
        </form>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Categoria</th>
                        <th>Estado</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Destacado</th>
                        <th>Banner</th>
                        <th>Carrusel</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>
                                {{ $product->name }}<br>
                                <small>{{ $product->primaryImage?->url }}</small>
                            </td>
                            <td>{{ $product->brand?->name ?? '-' }}</td>
                            <td>{{ $product->category?->full_name }}</td>
                            <td>{{ $product->status === 'active' ? 'Activo' : 'Inactivo' }}</td>
                            <td>${{ number_format((float)$product->price, 2) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ $product->is_featured ? 'Si' : 'No' }}</td>
                            <td>
                                {{ $product->show_in_main_banner ? 'Si' : 'No' }}
                                @if($product->show_in_main_banner && $product->main_banner_order)
                                    <br><small>Orden: {{ $product->main_banner_order }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $product->show_in_home_carousel ? 'Si' : 'No' }}
                                @if($product->show_in_home_carousel && $product->home_carousel_order)
                                    <br><small>Orden: {{ $product->home_carousel_order }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline" href="{{ route('admin.products.edit', $product) }}">Editar</a>
                                    @if(auth()->user()->hasPermission('delete_products'))
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Eliminar producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline" type="submit">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12">No hay productos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $products->links() }}</div>
    </section>
@endsection
