<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['items', 'payment'])
            ->latest()
            ->paginate(15);

        return view('cashier.orders', compact('orders'));
    }

    public function create(): View
    {
        return view('cashier.create-order', [
            'menus' => Menu::where('is_active', true)->orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'table_number' => ['required', 'string', 'max:30'],
            'payment_status' => ['required', 'in:belum_bayar,lunas'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $validated['items'] = collect($validated['items'])
            ->filter(fn ($item) => (int) $item['qty'] > 0)
            ->values()
            ->all();

        if (count($validated['items']) === 0) {
            return back()->withErrors(['items' => 'Pilih minimal satu menu.'])->withInput();
        }

        $menus = Menu::where('is_active', true)
            ->whereIn('id', collect($validated['items'])->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        $order = DB::transaction(function () use ($validated, $menus) {
            $order = Order::create([
                'code' => 'KSR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'customer_name' => $validated['customer_name'],
                'table_number' => $validated['table_number'],
                'source' => 'kasir',
                'status' => 'confirmed',
                'payment_status' => $validated['payment_status'],
                'confirmed_at' => now(),
            ]);

            $total = 0;
            foreach ($validated['items'] as $line) {
                $menu = $menus->get((int) $line['menu_id']);
                if (! $menu) {
                    continue;
                }

                $qty = (int) $line['qty'];
                $total += $menu->price * $qty;
                $order->items()->create([
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'category' => $menu->category,
                    'price' => $menu->price,
                    'qty' => $qty,
                ]);
            }

            $order->update(['total' => $total]);

            return $order;
        });

        return redirect()->route('cashier.orders.index')->with('status', 'Pesanan '.$order->code.' berhasil dibuat.');
    }

    public function markPaid(Order $order): RedirectResponse
    {
        $order->update(['payment_status' => 'lunas']);

        return back()->with('status', 'Pesanan ditandai lunas.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        if (! in_array($order->status, ['done', 'cancelled'], true)) {
            $order->update(['status' => 'cancelled']);
        }

        return back()->with('status', 'Pesanan dibatalkan.');
    }
}
