<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRoleRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\RoleChangeLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $recentRoleChanges = RoleChangeLog::query()
            ->with(['changedBy:id,name,email', 'targetUser:id,name,email'])
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'recentRoleChanges'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $payload = [
            'name' => $validated['name'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('admin.users.index')->with('status', 'Datos del usuario actualizados correctamente.');
    }

    public function updateRole(UserRoleRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $actor = $request->user();
        $newRole = $validated['role'];
        $oldRole = $user->role;

        if ($oldRole === 'admin' && $newRole !== 'admin' && $actor && $actor->id === $user->id) {
            return back()->with('status', 'No puedes quitarte a ti mismo el rol admin.');
        }

        if ($oldRole === 'admin' && $newRole !== 'admin') {
            $adminCount = User::query()->where('role', 'admin')->count();

            if ($adminCount <= 1) {
                return back()->with('status', 'No puedes quitar el ultimo usuario admin.');
            }
        }

        if ($oldRole === $newRole) {
            return back()->with('status', 'El usuario ya tiene ese rol.');
        }

        $user->update([
            'role' => $newRole,
            'is_admin' => $newRole === 'admin',
        ]);

        if ($actor) {
            RoleChangeLog::query()->create([
                'changed_by_user_id' => $actor->id,
                'target_user_id' => $user->id,
                'old_role' => $oldRole,
                'new_role' => $newRole,
            ]);
        }

        return back()->with('status', 'Rol actualizado correctamente.');
    }
}
