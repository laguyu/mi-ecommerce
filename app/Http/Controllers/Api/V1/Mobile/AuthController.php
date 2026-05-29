<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales invalidas.'],
            ]);
        }

        $tokenName = $validated['device_name'] ?? 'mobile-app';
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'ok' => true,
            'message' => 'Sesion iniciada.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->transformUser($user),
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'customer',
            'is_admin' => false,
        ]);

        $tokenName = $validated['device_name'] ?? 'mobile-app';
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'ok' => true,
            'message' => 'Cuenta creada correctamente.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->transformUser($user),
            ],
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Usuario autenticado.',
            'data' => [
                'user' => $this->transformUser($request->user()),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Sesion cerrada.',
            'data' => [],
        ]);
    }

    private function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }
}
