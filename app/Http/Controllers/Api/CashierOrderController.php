<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashierOrderController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Order::with(['items', 'payment'])->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'table_number' => ['required', 'string', 'max:30'],
            'payment_status' => ['required', 'in:belum_bayar,lunas'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $menus = Menu::where('is_active', true)
            ->whereIn('id', collect($validated['items'])->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        if ($menus->count() !== count($validated['items'])) {
            return response()->json(['message' => 'Ada menu yang tidak tersedia.'], 422);
        }

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

            return $order->load('items');
        });

        return response()->json(['data' => $order], 201);
    }

    public function markPaid(Order $order): JsonResponse
    {
        $order->update(['payment_status' => 'lunas']);

        return response()->json(['data' => $order->fresh(['items', 'payment'])]);
    }

    public function cancel(Order $order): JsonResponse
    {
        if (! in_array($order->status, ['done', 'cancelled'], true)) {
            $order->update(['status' => 'cancelled']);
        }

        return response()->json(['data' => $order->fresh(['items', 'payment'])]);
    }
}
