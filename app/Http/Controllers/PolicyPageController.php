<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\View\View;

class PolicyPageController extends Controller
{
    public function show(string $slug): View
    {
        $site = SiteSetting::current();

        $pages = [
            'privacidad' => [
                'title' => 'Políticas de Privacidad',
                'content' => (string) ($site->privacy_policy_content ?? ''),
            ],
            'terminos' => [
                'title' => 'Términos del servicio',
                'content' => (string) ($site->terms_of_service_content ?? ''),
            ],
            'envios' => [
                'title' => 'Políticas de envíos',
                'content' => (string) ($site->shipping_policy_content ?? ''),
            ],
            'reembolsos' => [
                'title' => 'Políticas de cambios y reembolsos',
                'content' => (string) ($site->refund_policy_content ?? ''),
            ],
        ];

        if (! array_key_exists($slug, $pages)) {
            abort(404);
        }

        return view('storefront.policy-page', [
            'pageTitle' => $pages[$slug]['title'],
            'pageContent' => $pages[$slug]['content'],
            'siteSettings' => $site,
        ]);
    }
}
