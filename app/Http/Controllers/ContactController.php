<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $recipientEmail = trim((string) data_get(SiteSetting::current(), 'footer_email', ''));

        $contactMessage = ContactMessage::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'recipient_email' => $recipientEmail !== '' ? $recipientEmail : null,
            'status' => 'stored',
        ]);

        if ($recipientEmail !== '') {
            try {
                Mail::to($recipientEmail)->send(new ContactMessageMail($contactMessage));

                $contactMessage->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'delivery_error' => null,
                ]);
            } catch (\Throwable $exception) {
                $contactMessage->update([
                    'status' => 'failed',
                    'delivery_error' => $exception->getMessage(),
                ]);

                Log::warning('No se pudo enviar el correo de contacto.', [
                    'contact_message_id' => $contactMessage->id,
                    'recipient_email' => $recipientEmail,
                    'error' => $exception->getMessage(),
                ]);
            }
        } else {
            Log::warning('Mensaje de contacto almacenado sin correo de destino configurado.', [
                'contact_message_id' => $contactMessage->id,
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => $recipientEmail !== ''
                ? 'Tu mensaje fue enviado y almacenado correctamente.'
                : 'Tu mensaje fue almacenado, pero no hay correo de destino configurado en el sitio.',
            'data' => [
                'id' => $contactMessage->id,
            ],
        ]);
    }
}