<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderFilterRequest;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use App\Services\OrderNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderNotificationService $orderNotificationService)
    {
    }

    public function index(OrderFilterRequest $request): View
    {
        $filters = $request->validated();

        $search = trim((string) ($filters['q'] ?? ''));
        $orders = Order::query()
            ->with('user:id,name,email')
            ->with('items:id,order_id,product_name,quantity,line_total')
            ->when($search !== '', function ($query) use ($search) {
                if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true) && mb_strlen($search) >= 3) {
                    $query->whereFullText(['order_number', 'customer_email', 'customer_full_name'], $search);
                    return;
                }

                $query->where(function ($nested) use ($search) {
                    $nested->where('order_number', 'like', "{$search}%")
                        ->orWhere('customer_email', 'like', "{$search}%")
                        ->orWhere('customer_full_name', 'like', "{$search}%");
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['payment_method']), fn ($query) => $query->where('payment_method', $filters['payment_method']))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => [
                'q' => $search,
                'status' => $filters['status'] ?? '',
                'payment_method' => $filters['payment_method'] ?? '',
            ],
        ]);
    }

    public function updateStatus(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        $validated = $request->validated();

        $previousStatus = (string) $order->status;
        $newStatus = (string) $validated['status'];

        $order->update([
            'status' => $newStatus,
        ]);

        if ($previousStatus !== $newStatus) {
            $this->orderNotificationService->sendStatusChangedToCustomer($order->fresh('items'), $previousStatus, $newStatus);
        }

        return back()->with('status', 'Estado del pedido actualizado.');
    }

    public function export(OrderFilterRequest $request): BinaryFileResponse
    {
        $filters = $request->validated();

        $search = trim((string) ($filters['q'] ?? ''));

        $orders = Order::query()
            ->with('user:id,name,email')
            ->with('items:id,order_id,product_name,quantity,line_total')
            ->when($search !== '', function ($query) use ($search) {
                if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true) && mb_strlen($search) >= 3) {
                    $query->whereFullText(['order_number', 'customer_email', 'customer_full_name'], $search);

                    return;
                }

                $query->where(function ($nested) use ($search) {
                    $nested->where('order_number', 'like', "{$search}%")
                        ->orWhere('customer_email', 'like', "{$search}%")
                        ->orWhere('customer_full_name', 'like', "{$search}%");
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['payment_method']), fn ($query) => $query->where('payment_method', $filters['payment_method']))
            ->orderByDesc('id')
            ->get();

        $fileName = 'pedidos_admin_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new OrdersExport($orders, true), $fileName);
    }

    public function show(Order $order): View
    {
        $order->load([
            'user:id,name,email',
            'items.product.primaryImage:id,product_id,url,alt_text,is_primary',
        ]);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function pdf(Order $order)
    {
        $order->load([
            'user:id,name,email',
            'items.product.primaryImage:id,product_id,url,alt_text,is_primary',
        ]);

        $pdf = Pdf::loadView('pdf.orders.show', [
            'order' => $order,
            'isAdmin' => true,
        ])->setPaper('a4');

        $pdf->setOption(['isRemoteEnabled' => true]);

        return $pdf->download('pedido_admin_' . $order->order_number . '.pdf');
    }
}
