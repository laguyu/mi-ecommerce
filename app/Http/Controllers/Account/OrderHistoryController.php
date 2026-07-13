<?php

namespace App\Http\Controllers\Account;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\DatabaseEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:pending_payment,paid,payment_failed'],
            'payment_method' => ['nullable', 'in:stripe,paypal,transferencia,efectivo'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));

        $orders = $request->user()->orders()
            ->with('items:id,order_id,product_name,quantity,line_total')
            ->when($search !== '', function ($query) use ($search) {
                if (DatabaseEngine::supportsFullText() && mb_strlen($search) >= 3) {
                    $query->whereFullText(['order_number', 'customer_email', 'customer_full_name'], $search);

                    return;
                }

                $query->where(function ($nested) use ($search) {
                    $nested->where('order_number', 'like', "{$search}%")
                        ->orWhere('customer_email', 'like', "{$search}%")
                        ->orWhere('customer_full_name', 'like', "{$search}%");
                });
            })
            ->when(! empty($validated['status']), fn ($query) => $query->where('status', $validated['status']))
            ->when(! empty($validated['payment_method']), fn ($query) => $query->where('payment_method', $validated['payment_method']))
            ->when(! empty($validated['date_from']), fn ($query) => $query->whereDate('created_at', '>=', $validated['date_from']))
            ->when(! empty($validated['date_to']), fn ($query) => $query->whereDate('created_at', '<=', $validated['date_to']))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('account.orders.index', [
            'orders' => $orders,
            'filters' => [
                'q' => $search,
                'status' => $validated['status'] ?? '',
                'payment_method' => $validated['payment_method'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
            ],
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $order->load([
            'items.product.primaryImage:id,product_id,url,alt_text,is_primary',
        ]);

        return view('account.orders.show', [
            'order' => $order,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:pending_payment,paid,payment_failed'],
            'payment_method' => ['nullable', 'in:stripe,paypal,transferencia,efectivo'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));

        $orders = $request->user()->orders()
            ->with('items:id,order_id,product_name,quantity,line_total')
            ->when($search !== '', function ($query) use ($search) {
                if (DatabaseEngine::supportsFullText() && mb_strlen($search) >= 3) {
                    $query->whereFullText(['order_number', 'customer_email', 'customer_full_name'], $search);

                    return;
                }

                $query->where(function ($nested) use ($search) {
                    $nested->where('order_number', 'like', "{$search}%")
                        ->orWhere('customer_email', 'like', "{$search}%")
                        ->orWhere('customer_full_name', 'like', "{$search}%");
                });
            })
            ->when(! empty($validated['status']), fn ($query) => $query->where('status', $validated['status']))
            ->when(! empty($validated['payment_method']), fn ($query) => $query->where('payment_method', $validated['payment_method']))
            ->when(! empty($validated['date_from']), fn ($query) => $query->whereDate('created_at', '>=', $validated['date_from']))
            ->when(! empty($validated['date_to']), fn ($query) => $query->whereDate('created_at', '<=', $validated['date_to']))
            ->orderByDesc('id')
            ->get();

        $fileName = 'mis_pedidos_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new OrdersExport($orders, false), $fileName);
    }

    public function pdf(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $order->load([
            'items.product.primaryImage:id,product_id,url,alt_text,is_primary',
        ]);

        $pdf = Pdf::loadView('pdf.orders.show', [
            'order' => $order,
            'isAdmin' => false,
        ])->setPaper('a4');

        $pdf->setOption(['isRemoteEnabled' => true]);

        return $pdf->download('pedido_' . $order->order_number . '.pdf');
    }
}
