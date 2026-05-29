<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:160'],
        ]);

        $subscriber = NewsletterSubscriber::query()->firstOrNew([
            'email' => mb_strtolower(trim($validated['email'])),
        ]);

        $subscriber->is_active = true;
        $subscriber->subscribed_at = $subscriber->subscribed_at ?? now();
        $subscriber->save();

        return response()->json([
            'message' => 'Te suscribiste correctamente al newsletter.',
        ]);
    }
}
