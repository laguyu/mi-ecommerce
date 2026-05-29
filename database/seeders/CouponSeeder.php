<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'BIENVENIDA10',
                'name' => 'Bienvenida 10%',
                'type' => 'percentage',
                'value' => 10,
                'status' => 'active',
                'starts_at' => null,
                'ends_at' => null,
                'max_uses' => null,
            ],
            [
                'code' => 'AHORRA5',
                'name' => 'Ahorra $5',
                'type' => 'fixed',
                'value' => 5,
                'status' => 'active',
                'starts_at' => null,
                'ends_at' => null,
                'max_uses' => null,
            ],
            [
                'code' => 'FINDE15',
                'name' => 'Promo fin de semana 15%',
                'type' => 'percentage',
                'value' => 15,
                'status' => 'active',
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addDays(7)->endOfDay(),
                'max_uses' => 200,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::query()->updateOrCreate(
                ['code' => $coupon['code']],
                [
                    'name' => $coupon['name'],
                    'type' => $coupon['type'],
                    'value' => $coupon['value'],
                    'status' => $coupon['status'],
                    'starts_at' => $coupon['starts_at'],
                    'ends_at' => $coupon['ends_at'],
                    'max_uses' => $coupon['max_uses'],
                ]
            );
        }
    }
}
