@extends('layouts.app', ['title' => 'Admin usuarios'])

@section('content')
    <style>
        .permission-badge { font-size: .75rem; border-radius: 999px; padding: .2rem .5rem; border: 1px solid #d1d5db; background: #f9fafb; color: #374151; }
        .permission-badge--view { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
        .permission-badge--manage { border-color: #bbf7d0; background: #ecfdf5; color: #047857; }
        .permission-badge--delete { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
    </style>

    <section class="card">
        <h1>Panel admin - Usuarios</h1>

        <div class="actions" style="margin-top:.5rem; margin-bottom:.7rem; flex-wrap: wrap;">
            <span class="permission-badge permission-badge--view">Lectura (view_*)</span>
            <span class="permission-badge permission-badge--manage">Gestion (manage_*)</span>
            <span class="permission-badge permission-badge--delete">Eliminacion (delete_*)</span>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="grid" style="grid-template-columns: 1fr auto auto; margin-top: .9rem;">
            <input class="input" type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar por nombre o correo">
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-outline" href="{{ route('admin.users.index') }}">Limpiar</a>
        </form>

        <div style="overflow-x:auto; margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Permisos</th>
                        <th>Actualizar rol</th>
                        <th>Perfil</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td>
                                <div class="actions" style="flex-wrap: wrap;">
                                    @forelse($user->permissionList() as $permission)
                                        @php
                                            $badgeClass = 'permission-badge';

                                            if (str_starts_with($permission, 'view_')) {
                                                $badgeClass .= ' permission-badge--view';
                                            }

                                            if (str_starts_with($permission, 'manage_')) {
                                                $badgeClass .= ' permission-badge--manage';
                                            }

                                            if (str_starts_with($permission, 'delete_')) {
                                                $badgeClass .= ' permission-badge--delete';
                                            }
                                        @endphp

                                        <span class="{{ $badgeClass }}">{{ $permission }}</span>
                                    @empty
                                        <span style="font-size:.75rem; color:#6b7280;">Sin permisos admin</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="actions" style="align-items:center;">
                                    @csrf
                                    @method('PATCH')
                                    <select class="select" name="role" style="min-width: 140px; max-width: 170px;">
                                        <option value="admin" @selected($user->role === 'admin')>admin</option>
                                        <option value="editor" @selected($user->role === 'editor')>editor</option>
                                        <option value="soporte" @selected($user->role === 'soporte')>soporte</option>
                                        <option value="customer" @selected($user->role === 'customer')>customer</option>
                                    </select>
                                    <button class="btn" type="submit">Guardar</button>
                                </form>
                            </td>
                            <td>
                                <a class="btn btn-outline" href="{{ route('admin.users.edit', $user) }}">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay usuarios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $users->links() }}</div>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h2>Auditoria de cambios de rol</h2>

        <div style="overflow-x:auto; margin-top:.8rem;">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cambiado por</th>
                        <th>Usuario objetivo</th>
                        <th>Rol anterior</th>
                        <th>Rol nuevo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRoleChanges as $log)
                        <tr>
                            <td>{{ $log->created_at->toDateTimeString() }}</td>
                            <td>{{ $log->changedBy?->name }}<br><small>{{ $log->changedBy?->email }}</small></td>
                            <td>{{ $log->targetUser?->name }}<br><small>{{ $log->targetUser?->email }}</small></td>
                            <td>{{ $log->old_role }}</td>
                            <td>{{ $log->new_role }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Aun no hay cambios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $recentRoleChanges->links() }}</div>
    </section>
@endsection
