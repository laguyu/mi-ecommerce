<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EcommerceSeeder::class,
            CouponSeeder::class,
        ]);

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
