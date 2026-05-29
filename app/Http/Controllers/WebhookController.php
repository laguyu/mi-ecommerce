<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    public function __construct(private readonly OrderPaymentService $orderPaymentService)
    {
    }

    public function stripe(Request $request): JsonResponse
    {
        $payload = (string) $request->getContent();
        $signatureHeader = (string) $request->header('Stripe-Signature', '');
        $secret = (string) config('services.stripe.webhook_secret');

        if ($secret === '' || ! $this->isValidStripeSignature($payload, $signatureHeader, $secret)) {
            return response()->json(['message' => 'Firma Stripe invalida.'], 400);
        }

        $event = json_decode($payload, true);

        if (! is_array($event)) {
            return response()->json(['message' => 'Payload Stripe invalido.'], 400);
        }

        $eventType = (string) ($event['type'] ?? '');

        if (! in_array($eventType, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return response()->json(['received' => true]);
        }

        $session = $event['data']['object'] ?? [];
        $paymentStatus = (string) ($session['payment_status'] ?? '');

        if (! in_array($paymentStatus, ['paid', 'no_payment_required'], true)) {
            return response()->json(['received' => true]);
        }

        $orderId = (int) ($session['metadata']['order_id'] ?? 0);

        if ($orderId <= 0) {
            return response()->json(['received' => true]);
        }

        $order = Order::query()->find($orderId);

        if (! $order) {
            return response()->json(['received' => true]);
        }

        $reference = (string) ($session['payment_intent'] ?? $session['id'] ?? 'stripe-webhook');
        $this->orderPaymentService->markOrderAsPaid($order, 'stripe', $reference);

        return response()->json(['received' => true]);
    }

    public function paypal(Request $request): JsonResponse
    {
        $secret = (string) config('services.paypal.client_secret');
        $clientId = (string) config('services.paypal.client_id');
        $webhookId = (string) config('services.paypal.webhook_id');

        if ($secret === '' || $clientId === '' || $webhookId === '') {
            return response()->json(['message' => 'Configuracion PayPal webhook incompleta.'], 400);
        }

        $payload = $request->json()->all();

        if (! is_array($payload) || ! $this->isValidPaypalWebhook($request, $payload, $clientId, $secret, $webhookId)) {
            return response()->json(['message' => 'Firma PayPal invalida.'], 400);
        }

        $eventType = (string) ($payload['event_type'] ?? '');

        if ($eventType !== 'PAYMENT.CAPTURE.COMPLETED') {
            return response()->json(['received' => true]);
        }

        $paypalOrderId = (string) ($payload['resource']['supplementary_data']['related_ids']['order_id'] ?? $payload['resource']['id'] ?? '');

        if ($paypalOrderId === '') {
            return response()->json(['received' => true]);
        }

        $token = $this->paypalAccessToken($clientId, $secret);

        if (! $token) {
            return response()->json(['message' => 'No se pudo autenticar contra PayPal.'], 400);
        }

        $orderDetails = Http::withToken($token)
            ->get($this->paypalBaseUrl().'/v2/checkout/orders/'.$paypalOrderId);

        if (! $orderDetails->successful()) {
            return response()->json(['received' => true]);
        }

        $customId = (string) ($orderDetails->json('purchase_units.0.custom_id') ?? '');
        $reference = (string) ($orderDetails->json('id') ?? $paypalOrderId);

        if ($customId === '') {
            return response()->json(['received' => true]);
        }

        $order = Order::query()->find((int) $customId);

        if (! $order) {
            return response()->json(['received' => true]);
        }

        $this->orderPaymentService->markOrderAsPaid($order, 'paypal', $reference);

        return response()->json(['received' => true]);
    }

    private function isValidStripeSignature(string $payload, string $signatureHeader, string $secret): bool
    {
        if ($signatureHeader === '') {
            return false;
        }

        $timestamp = 0;
        $expectedSignatures = [];

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');

            if ($key === 't') {
                $timestamp = (int) $value;
            }

            if ($key === 'v1' && $value !== '') {
                $expectedSignatures[] = $value;
            }
        }

        if ($timestamp <= 0 || empty($expectedSignatures)) {
            return false;
        }

        $tolerance = (int) config('services.stripe.webhook_tolerance', 300);

        if (abs(time() - $timestamp) > $tolerance) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;
        $computed = hash_hmac('sha256', $signedPayload, $secret);

        foreach ($expectedSignatures as $candidate) {
            if (hash_equals($computed, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function isValidPaypalWebhook(Request $request, array $payload, string $clientId, string $secret, string $webhookId): bool
    {
        $token = $this->paypalAccessToken($clientId, $secret);

        if (! $token) {
            return false;
        }

        $verification = Http::withToken($token)
            ->post($this->paypalBaseUrl().'/v1/notifications/verify-webhook-signature', [
                'transmission_id' => $request->header('Paypal-Transmission-Id'),
                'transmission_time' => $request->header('Paypal-Transmission-Time'),
                'cert_url' => $request->header('Paypal-Cert-Url'),
                'auth_algo' => $request->header('Paypal-Auth-Algo'),
                'transmission_sig' => $request->header('Paypal-Transmission-Sig'),
                'webhook_id' => $webhookId,
                'webhook_event' => $payload,
            ]);

        return $verification->successful() && $verification->json('verification_status') === 'SUCCESS';
    }

    private function paypalAccessToken(string $clientId, string $secret): ?string
    {
        $response = Http::asForm()
            ->withBasicAuth($clientId, $secret)
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
