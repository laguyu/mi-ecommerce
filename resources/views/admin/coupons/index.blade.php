@extends('layouts.app', ['title' => 'Admin cupones'])

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.7rem; flex-wrap:wrap;">
            <h1>Panel admin - Cupones</h1>
            <a class="btn" href="{{ route('admin.coupons.create') }}">Nuevo cupon</a>
        </div>

        <form method="GET" action="{{ route('admin.coupons.index') }}" class="grid" style="grid-template-columns: 1fr auto auto; margin-top: .9rem;">
            <input class="input" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar por nombre o codigo">
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-outline" href="{{ route('admin.coupons.index') }}">Limpiar</a>
        </form>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th>Vigencia</th>
                        <th>Usos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td><strong>{{ $coupon->code }}</strong></td>
                            <td>{{ $coupon->name }}</td>
                            <td>{{ $coupon->type === 'percentage' ? 'Porcentaje' : 'Monto fijo' }}</td>
                            <td>{{ $coupon->type === 'percentage' ? rtrim(rtrim(number_format((float) $coupon->value, 2, '.', ''), '0'), '.') . '%' : '$' . number_format((float) $coupon->value, 2) }}</td>
                            <td>{{ $coupon->status === 'active' ? 'Activo' : 'Inactivo' }}</td>
                            <td>
                                <small>
                                    {{ $coupon->starts_at ? $coupon->starts_at->format('Y-m-d H:i') : 'Sin inicio' }}<br>
                                    {{ $coupon->ends_at ? $coupon->ends_at->format('Y-m-d H:i') : 'Sin fin' }}
                                </small>
                            </td>
                            <td>{{ $coupon->used_count }}{{ $coupon->max_uses ? (' / ' . $coupon->max_uses) : '' }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline" href="{{ route('admin.coupons.edit', $coupon) }}">Editar</a>
                                    @if(auth()->user()->hasPermission('delete_products'))
                                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Eliminar cupon?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline" type="submit">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No hay cupones creados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $coupons->links() }}</div>
    </section>
@endsection
