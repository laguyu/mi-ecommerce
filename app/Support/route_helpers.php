<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

if (! function_exists('storefront_view')) {
    function storefront_view(string $initialView = 'home', ?int $productId = null)
    {
        return view('welcome', [
            'isBackoffice' => false,
            'initialStorefrontView' => $initialView,
            'initialStorefrontProductId' => $productId,
        ]);
    }
}

if (! function_exists('backoffice_dashboard_view')) {
    function backoffice_dashboard_view()
    {
        $user = request()->user();

        if (! $user || $user->role === 'customer') {
            abort(403);
        }

        $stats = [
            'orders_today' => null,
            'orders_pending' => null,
            'products' => null,
            'categories' => null,
            'users' => null,
        ];

        if ($user->hasPermission('view_admin_orders')) {
            $stats['orders_today'] = Order::query()->whereDate('created_at', today())->count();
            $stats['orders_pending'] = Order::query()->where('status', 'pending_payment')->count();
        }

        if ($user->hasPermission('manage_products')) {
            $stats['products'] = Product::query()->count();
        }

        if ($user->hasPermission('manage_categories')) {
            $stats['categories'] = Category::query()->count();
        }

        if ($user->hasPermission('manage_users')) {
            $stats['users'] = User::query()->count();
        }

        return view('welcome', [
            'isBackoffice' => true,
            'stats' => $stats,
        ]);
    }
}
