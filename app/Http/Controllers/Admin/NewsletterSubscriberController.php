<?php

namespace App\Http\Controllers\Admin;

use App\Exports\NewsletterSubscribersExport;
use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $subscribers = NewsletterSubscriber::query()
            ->when($search !== '', fn ($query) => $query->where('email', 'like', "%{$search}%"))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.newsletter-subscribers.index', compact('subscribers', 'search'));
    }

    public function toggle(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->update([
            'is_active' => ! $newsletterSubscriber->is_active,
        ]);

        return redirect()->route('admin.newsletter-subscribers.index')->with('status', 'Estado del suscriptor actualizado.');
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->delete();

        return redirect()->route('admin.newsletter-subscribers.index')->with('status', 'Suscriptor eliminado.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $subscribers = NewsletterSubscriber::query()
            ->when($search !== '', fn ($query) => $query->where('email', 'like', "%{$search}%"))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('id')
            ->get();

        $timestamp = now()->format('Ymd_His');
        $statusLabel = match ($status) {
            'active' => 'activos',
            'inactive' => 'inactivos',
            default => 'todos',
        };

        $hasSearch = $search !== '';
        $filename = $hasSearch
            ? "newsletter_suscriptores_{$statusLabel}_filtrado_{$timestamp}.xlsx"
            : "newsletter_suscriptores_{$statusLabel}_{$timestamp}.xlsx";

        return Excel::download(new NewsletterSubscribersExport($subscribers), $filename);
    }
}
