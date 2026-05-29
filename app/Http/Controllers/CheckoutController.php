<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\OrderNotificationService;
use App\Services\ProductPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderNotificationService $orderNotificationService,
        private readonly ProductPricingService $productPricingService
    )
    {
    }

    public function prepare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer.fullName' => ['required', 'string', 'max:120'],
            'customer.email' => ['required', 'email'],
            'customer.address' => ['nullable', 'string', 'max:255'],
            'customer.city' => ['nullable', 'string', 'max:80'],
            'customer.postalCode' => ['nullable', 'string', 'max:30'],
            'shippingMethod' => ['required', 'in:pickup,delivery'],
            'couponCode' => ['nullable', 'string', 'max:40'],
            'paymentMethod' => ['required', 'in:stripe,paypal,transferencia,efectivo'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $isDelivery = $validated['shippingMethod'] === 'delivery';
        $address = trim((string) ($validated['customer']['address'] ?? ''));
        $city = trim((string) ($validated['customer']['city'] ?? ''));
        $postalCode = trim((string) ($validated['customer']['postalCode'] ?? ''));

        if ($isDelivery && ($address === '' || $city === '' || $postalCode === '')) {
            return response()->json([
                'message' => 'Para delivery debes completar dirección, ciudad y código postal.',
            ], 422);
        }

        $productIds = collect($validated['items'])->pluck('id')->unique()->values();
        $productsById = Product::query()
            ->whereIn('id', $productIds)
            ->with(['promotions' => function ($query): void {
                $query->activeNow()
                    ->select('promotions.id', 'promotions.name', 'promotions.discount_percentage', 'promotions.status', 'promotions.starts_at', 'promotions.ends_at')
                    ->orderByDesc('discount_percentage')
                    ->orderByDesc('starts_at')
                    ->orderByDesc('id');
            }])
            ->get()
            ->keyBy('id');

        $normalizedItems = [];
        $promotionDiscountAmount = 0.0;

        foreach ($validated['items'] as $item) {
            /** @var Product|null $product */
            $product = $productsById->get($item['id']);

            if (! $product) {
                return response()->json([
                    'message' => 'Producto inválido en carrito.',
                ], 422);
            }

            if ($product->stock < (int) $item['quantity']) {
                return response()->json([
                    'message' => "Stock insuficiente para {$product->name}.",
                ], 422);
            }

            $pricing = $this->productPricingService->pricingForProduct($product);
            $originalPrice = (float) ($pricing['original_price'] ?? $product->price);
            $finalPrice = (float) ($pricing['final_price'] ?? $product->price);
            $quantity = (int) $item['quantity'];

            $promotionDiscountAmount += max($originalPrice - $finalPrice, 0) * $quantity;

            $normalizedItems[] = [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $finalPrice,
                'quantity' => $quantity,
            ];
        }

        $subtotal = collect($normalizedItems)->sum(fn ($item) => $item['price'] * $item['quantity']);

        $couponCodeInput = trim((string) ($validated['couponCode'] ?? ''));
        $couponResolution = $this->resolveCouponDiscount($couponCodeInput, (float) $subtotal);
        if (!empty($couponCodeInput) && ! $couponResolution['coupon']) {
            return response()->json([
                'message' => $couponResolution['error'] ?? 'El cupon no es valido.',
            ], 422);
        }

        /** @var Coupon|null $coupon */
        $coupon = $couponResolution['coupon'];
        $discountRate = $couponResolution['discount_rate'];
        $discountAmount = $couponResolution['discount_amount'];
        $subtotalAfterDiscount = $subtotal;
        if ($discountAmount > 0) {
            $subtotalAfterDiscount = max($subtotal - $discountAmount, 0);
        }

        $siteSettings = SiteSetting::current();
        $deliveryFee = max((float) ($siteSettings->delivery_fee ?? 7.99), 0);
        $freeShippingThreshold = max((float) ($siteSettings->free_shipping_threshold ?? 120), 0);

        $shippingAmount = $validated['shippingMethod'] === 'pickup'
            ? 0
            : ($subtotalAfterDiscount >= $freeShippingThreshold ? 0 : $deliveryFee);
        $total = $subtotalAfterDiscount + $shippingAmount;

        $order = DB::transaction(function () use ($request, $validated, $normalizedItems, $subtotal, $discountRate, $discountAmount, $promotionDiscountAmount, $shippingAmount, $total, $address, $city, $postalCode, $coupon) {
            $order = Order::query()->create([
            'user_id' => $request->user()?->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_full_name' => $validated['customer']['fullName'],
                'customer_email' => $validated['customer']['email'],
                'customer_address' => $address !== '' ? $address : 'RETIRO EN TIENDA',
                'customer_city' => $city !== '' ? $city : 'PICKUP',
                'customer_postal_code' => $postalCode !== '' ? $postalCode : '00000',
                'coupon_code' => $coupon?->code,
                'discount_rate' => round($discountRate * 100, 2),
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'promotion_discount_amount' => round($promotionDiscountAmount, 2),
                'coupon_discount_amount' => round($discountAmount, 2),
                'shipping_amount' => round($shippingAmount, 2),
                'total' => round($total, 2),
                'status' => 'pending_payment',
                'payment_method' => $validated['paymentMethod'],
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            foreach ($normalizedItems as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'sku' => $item['sku'],
                    'product_name' => $item['name'],
                    'unit_price' => round($item['price'], 2),
                    'quantity' => $item['quantity'],
                    'line_total' => round($item['price'] * $item['quantity'], 2),
                ]);
            }

            return $order;
        });

        if (in_array($validated['paymentMethod'], ['transferencia', 'efectivo'], true)) {
            $this->orderNotificationService->sendForSuccessfulOrder($order, false);
        }

        return response()->json([
            'message' => 'Pedido preparado. Continúa con el pago.',
            'order' => [
                'id' => $order->id,
                'number' => $order->order_number,
                'date' => now()->toDateTimeString(),
                'items' => count($normalizedItems),
                'subtotal' => round($subtotal, 2),
                'discountAmount' => round($discountAmount, 2),
                'promotionDiscountAmount' => round($promotionDiscountAmount, 2),
                'couponDiscountAmount' => round($discountAmount, 2),
                'totalDiscountAmount' => round($promotionDiscountAmount + $discountAmount, 2),
                'subtotalAfterDiscount' => round($subtotalAfterDiscount, 2),
                'shippingAmount' => round($shippingAmount, 2),
                'total' => round($total, 2),
                'paymentMethod' => $validated['paymentMethod'],
                'shippingMethod' => $validated['shippingMethod'],
            ],
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $resolution = $this->resolveCouponDiscount((string) $validated['code'], (float) $validated['subtotal']);

        if (! $resolution['coupon']) {
            return response()->json([
                'valid' => false,
                'message' => $resolution['error'] ?? 'Cupon no valido.',
            ], 422);
        }

        /** @var Coupon $coupon */
        $coupon = $resolution['coupon'];

        return response()->json([
            'valid' => true,
            'message' => 'Cupon aplicado correctamente.',
            'data' => [
                'code' => $coupon->code,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'discountAmount' => round($resolution['discount_amount'], 2),
                'discountRate' => round($resolution['discount_rate'] * 100, 2),
            ],
        ]);
    }

    public function summary(Order $order): JsonResponse
    {
        if (Auth::check() && $order->user_id && $order->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        $order->loadMissing(['items.product.primaryImage:id,product_id,url,alt_text,is_primary']);

        return response()->json([
            'data' => [
                'id' => $order->id,
                'number' => $order->order_number,
                'date' => optional($order->paid_at)->toDateTimeString() ?? $order->created_at->toDateTimeString(),
                'items' => $order->items()->count(),
                'lines' => $order->items
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'quantity' => (int) $item->quantity,
                        'unitPrice' => (float) $item->unit_price,
                        'lineTotal' => (float) $item->line_total,
                        'image' => $item->product?->primaryImage?->url,
                    ])
                    ->values(),
                'subtotal' => (float) $order->subtotal,
                'discountAmount' => (float) $order->discount_amount,
                'promotionDiscountAmount' => (float) ($order->promotion_discount_amount ?? 0),
                'couponDiscountAmount' => (float) ($order->coupon_discount_amount ?? $order->discount_amount),
                'totalDiscountAmount' => (float) (($order->promotion_discount_amount ?? 0) + ($order->coupon_discount_amount ?? $order->discount_amount)),
                'shippingAmount' => (float) $order->shipping_amount,
                'total' => (float) $order->total,
                'status' => $order->status,
                'paymentMethod' => $order->payment_method,
            ],
        ]);
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function resolveCouponDiscount(string $couponCode, float $subtotal): array
    {
        $normalizedCode = mb_strtoupper(trim($couponCode));

        if ($normalizedCode === '') {
            return [
                'coupon' => null,
                'discount_amount' => 0,
                'discount_rate' => 0,
                'error' => null,
            ];
        }

        /** @var Coupon|null $coupon */
        $coupon = Coupon::query()->whereRaw('UPPER(code) = ?', [$normalizedCode])->first();

        if (! $coupon) {
            return [
                'coupon' => null,
                'discount_amount' => 0,
                'discount_rate' => 0,
                'error' => 'El cupon no existe.',
            ];
        }

        if (! $coupon->isActiveNow()) {
            return [
                'coupon' => null,
                'discount_amount' => 0,
                'discount_rate' => 0,
                'error' => 'El cupon esta inactivo o fuera de vigencia.',
            ];
        }

        $discountAmount = $coupon->calculateDiscountAmount($subtotal);
        $discountRate = $subtotal > 0 ? ($discountAmount / $subtotal) : 0;

        return [
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
            'discount_rate' => $discountRate,
            'error' => null,
        ];
    }
}
