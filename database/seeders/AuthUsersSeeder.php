<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AuthUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@miecommerce.test'],
            [
                'name' => 'Admin Ecommerce',
                'password' => 'password',
                'is_admin' => true,
                'role' => 'admin',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'editor@miecommerce.test'],
            [
                'name' => 'Editor Ecommerce',
                'password' => 'password',
                'is_admin' => false,
                'role' => 'editor',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'soporte@miecommerce.test'],
            [
                'name' => 'Soporte Ecommerce',
                'password' => 'password',
                'is_admin' => false,
                'role' => 'soporte',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'cliente@miecommerce.test'],
            [
                'name' => 'Cliente Demo',
                'password' => 'password',
                'is_admin' => false,
                'role' => 'customer',
            ]
        );
    }
}