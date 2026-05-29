<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use stdClass;

class PaymentController extends Controller
{
    public function __construct(private readonly OrderPaymentService $orderPaymentService)
    {
    }

    public function createStripeCheckoutSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderId' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = Order::query()->with('items')->findOrFail($validated['orderId']);

        if ($order->status !== 'pending_payment' || $order->payment_method !== 'stripe') {
            return response()->json([
                'message' => 'La orden no está lista para pago con Stripe.',
            ], 422);
        }

        $stripeSecret = (string) config('services.stripe.secret_key');

        if ($stripeSecret === '') {
            return response()->json([
                'message' => 'Falta configurar STRIPE_SECRET_KEY en .env',
            ], 422);
        }

        $currency = strtolower((string) config('services.stripe.currency', 'usd'));

        $lineItems = [];

        foreach ($order->items as $index => $item) {
            $lineItems["line_items[{$index}][price_data][currency]"] = $currency;
            $lineItems["line_items[{$index}][price_data][product_data][name]"] = $item->product_name;
            $lineItems["line_items[{$index}][price_data][unit_amount]"] = (int) round(((float) $item->unit_price) * 100);
            $lineItems["line_items[{$index}][quantity]"] = (int) $item->quantity;
        }

        if ((float) $order->shipping_amount > 0) {
            $index = count($order->items);
            $lineItems["line_items[{$index}][price_data][currency]"] = $currency;
            $lineItems["line_items[{$index}][price_data][product_data][name]"] = 'Costo de envio';
            $lineItems["line_items[{$index}][price_data][unit_amount]"] = (int) round(((float) $order->shipping_amount) * 100);
            $lineItems["line_items[{$index}][quantity]"] = 1;
        }

        $payload = array_merge($lineItems, [
            'mode' => 'payment',
            'success_url' => route('checkout.stripe.success', ['order' => $order->id]).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', ['order' => $order->id]),
            'metadata[order_id]' => (string) $order->id,
            'payment_intent_data[metadata][order_id]' => (string) $order->id,
        ]);

        $response = Http::asForm()
            ->withBasicAuth($stripeSecret, '')
            ->post('https://api.stripe.com/v1/checkout/sessions', $payload);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'No se pudo crear la sesión de pago en Stripe.',
                'provider_error' => $response->json(),
            ], 422);
        }

        return response()->json([
            'redirectUrl' => $response->json('url'),
        ]);
    }

    public function stripeSuccess(Request $request, Order $order): RedirectResponse
    {
        $sessionId = (string) $request->query('session_id', '');
        $stripeSecret = (string) config('services.stripe.secret_key');

        if ($sessionId === '' || $stripeSecret === '') {
            return redirect('/?checkout=failed&order='.$order->id);
        }

        $response = Http::withBasicAuth($stripeSecret, '')
            ->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId);

        if (! $response->successful()) {
            return redirect('/?checkout=failed&order='.$order->id);
        }

        $paymentStatus = (string) $response->json('payment_status');
        $metadataOrderId = (string) $response->json('metadata.order_id');
        $paymentReference = (string) ($response->json('payment_intent') ?? $response->json('id'));

        if ($paymentStatus !== 'paid' || (int) $metadataOrderId !== $order->id) {
            return redirect('/?checkout=failed&order='.$order->id);
        }

        $finalized = $this->orderPaymentService->markOrderAsPaid($order, 'stripe', $paymentReference);

        return redirect($finalized ? '/?checkout=success&order='.$order->id : '/?checkout=failed&order='.$order->id);
    }

    public function createPaypalOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderId' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = Order::query()->with('items')->findOrFail($validated['orderId']);

        if ($order->status !== 'pending_payment' || $order->payment_method !== 'paypal') {
            return response()->json([
                'message' => 'La orden no está lista para pago con PayPal.',
            ], 422);
        }

        $token = $this->paypalAccessToken();

        if (! $token) {
            return response()->json([
                'message' => 'Falta configurar PAYPAL_CLIENT_ID y PAYPAL_CLIENT_SECRET en .env',
            ], 422);
        }

        $currency = (string) config('services.paypal.currency', 'USD');
        $baseUrl = $this->paypalBaseUrl();
        $amountValue = number_format((float) $order->total, 2, '.', '');

        $response = Http::withToken($token)
            ->withHeaders([
                'Prefer' => 'return=representation',
            ])
            ->post($baseUrl.'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $order->order_number,
                    'custom_id' => (string) $order->id,
                    'description' => 'Pago de pedido '.$order->order_number,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $amountValue,
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'brand_name' => (string) config('app.name'),
                            'landing_page' => 'LOGIN',
                            'user_action' => 'PAY_NOW',
                            'return_url' => route('checkout.paypal.return', ['order' => $order->id]),
                            'cancel_url' => route('checkout.cancel', ['order' => $order->id]),
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'No se pudo crear la orden de PayPal.',
                'paypal_debug_id' => $response->header('Paypal-Debug-Id'),
                'provider_error' => $response->json(),
            ], 422);
        }

        $links = collect($response->json('links', []));

        $approveUrl = $links
            ->first(fn ($link) => in_array(($link['rel'] ?? ''), ['approve', 'payer-action'], true))['href'] ?? null;

        if (! $approveUrl) {
            return response()->json([
                'message' => 'PayPal no devolvió URL de aprobación.',
                'paypal_debug_id' => $response->header('Paypal-Debug-Id'),
                'provider_links' => $links->values()->all(),
            ], 422);
        }

        $order->update([
            'payment_reference' => $response->json('id'),
        ]);

        return response()->json([
            'redirectUrl' => $approveUrl,
        ]);
    }

    public function paypalReturn(Request $request, Order $order): RedirectResponse
    {
        $paypalOrderId = (string) $request->query('token', '');

        if ($paypalOrderId === '') {
            return redirect('/?checkout=failed&order='.$order->id);
        }

        $token = $this->paypalAccessToken();

        if (! $token) {
            return redirect('/?checkout=failed&order='.$order->id);
        }

        $response = Http::withToken($token)
            ->post($this->paypalBaseUrl().'/v2/checkout/orders/'.$paypalOrderId.'/capture', new stdClass);

        if (! $response->successful()) {
            return redirect('/?checkout=failed&order='.$order->id);
        }

        $status = (string) $response->json('status');

        if ($status !== 'COMPLETED') {
            return redirect('/?checkout=failed&order='.$order->id);
        }

        $finalized = $this->orderPaymentService->markOrderAsPaid($order, 'paypal', $paypalOrderId);

        return redirect($finalized ? '/?checkout=success&order='.$order->id : '/?checkout=failed&order='.$order->id);
    }

    public function cancel(Order $order): RedirectResponse
    {
        return redirect('/?checkout=cancelled&order='.$order->id);
    }

    private function paypalAccessToken(): ?string
    {
        $clientId = (string) config('services.paypal.client_id');
        $clientSecret = (string) config('services.paypal.client_secret');

        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post($this->paypalBaseUrl().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            return null;
        }

        return (string) $response->json('access_token');
    }

    private function paypalBaseUrl(): string
    {
        $mode = (string) config('services.paypal.mode', 'sandbox');

        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}
