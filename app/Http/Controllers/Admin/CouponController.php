<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $coupons = Coupon::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.coupons.index', compact('coupons', 'search'));
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Coupon::query()->create([
            'code' => Str::upper(trim($validated['code'])),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'status' => $validated['status'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'used_count' => 0,
        ]);

        return redirect()->route('admin.coupons.index')->with('status', 'Cupon creado correctamente.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validated();

        $coupon->update([
            'code' => Str::upper(trim($validated['code'])),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'status' => $validated['status'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
        ]);

        return redirect()->route('admin.coupons.index')->with('status', 'Cupon actualizado correctamente.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        if (! request()->user()?->hasPermission('delete_products')) {
            abort(403, 'No autorizado.');
        }

        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('status', 'Cupon eliminado correctamente.');
    }
}
